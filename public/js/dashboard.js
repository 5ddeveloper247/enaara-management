(function () {
    'use strict';

    // ============================================
    // UTILITIES
    // ============================================
    const DashboardUtils = {
        /**
         * Get theme colors from CSS variables
         */
        getThemeColors() {
            const rootStyles = getComputedStyle(document.documentElement);
            return {
                primaryColor: rootStyles.getPropertyValue('--main-color').trim() || '#e6c673',
                mainColor: rootStyles.getPropertyValue('--secondary-color').trim() || '#012445'
            };
        },

        /**
         * Convert hex color to RGB
         */
        hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        },

        /**
         * Convert RGB to hex color
         */
        rgbToHex(r, g, b) {
            return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
        },

        /**
         * Lighten a color by percentage
         */
        lightenColor(hex, percent) {
            const rgb = this.hexToRgb(hex);
            if (!rgb) return hex;
            const r = Math.min(255, Math.round(rgb.r + (255 - rgb.r) * percent));
            const g = Math.min(255, Math.round(rgb.g + (255 - rgb.g) * percent));
            const b = Math.min(255, Math.round(rgb.b + (255 - rgb.b) * percent));
            return this.rgbToHex(r, g, b);
        },

        /**
         * Convert hex to rgba
         */
        hexToRgba(hex, alpha = 0.3) {
            const rgb = this.hexToRgb(hex);
            if (!rgb) return `rgba(0, 0, 0, ${alpha})`;
            return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${alpha})`;
        },

        /**
         * Show alert message
         */
        showAlert(message, type = 'info') {
            alert(message);
        },

        /**
         * Confirm action
         */
        confirmAction(message) {
            return confirm(message);
        }
    };

    // ============================================
    // CHARTS
    // ============================================
    const DashboardCharts = {
        attendanceChart: null,
        departmentChart: null,

        /**
         * Initialize Attendance Overview Chart with real data
         */
        initAttendanceChart() {
            const ctx = document.getElementById('attendanceChart');
            if (!ctx) return;

            const colors = DashboardUtils.getThemeColors();

            this.attendanceChart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Present',
                        data: [],
                        backgroundColor: colors.primaryColor,
                        borderWidth: 0,
                        borderRadius: 1000
                    }, {
                        label: 'Absent',
                        data: [],
                        backgroundColor: '#dc3545',
                        borderWidth: 0,
                        borderRadius: 1000
                    }, {
                        label: 'On Leave',
                        data: [],
                        backgroundColor: colors.mainColor,
                        borderWidth: 0,
                        borderRadius: 1000
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        axis: 'y',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#000000',
                                usePointStyle: true,
                                boxWidth: 10,
                                boxHeight: 10,
                                padding: 15
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            axis: 'y',
                            intersect: false,
                            titleColor: '#000000',
                            bodyColor: '#000000',
                            borderColor: 'rgba(0, 0, 0, 0.2)',
                            backgroundColor: 'rgba(255, 255, 255, 0.95)'
                        }
                    },
                    layout: {
                        padding: {
                            left: 0,
                            right: 0,
                            top: 0,
                            bottom: 0
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            beginAtZero: true,
                            ticks: {
                                stepSize: 20,
                                color: '#000000'
                            },
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            stacked: true,
                            ticks: {
                                color: '#000000',
                                autoSkip: false
                            },
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            categoryPercentage: 1,
                            barPercentage: 0.95
                        }
                    }
                }
            });

            this.loadAttendanceData(7);
        },

        /**
         * Fetch attendance data from API and update chart
         */
        loadAttendanceData(period) {
            if (!this.attendanceChart) return;

            const url = (window._dashRoutes && window._dashRoutes.attendanceChart) ?
                window._dashRoutes.attendanceChart + '?period=' + period :
                '/admin/dashboard/attendance-chart?period=' + period;

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function (res) {
                    return res.json();
                })
                .then(function (json) {
                    if (!json.success) return;
                    const data = json.data;
                    DashboardCharts.attendanceChart.data.labels = data.labels;
                    DashboardCharts.attendanceChart.data.datasets[0].data = data.present;
                    DashboardCharts.attendanceChart.data.datasets[1].data = data.absent;
                    DashboardCharts.attendanceChart.data.datasets[2].data = data.onLeave;
                    DashboardCharts.attendanceChart.update();
                })
                .catch(function (err) {
                    console.error('Attendance chart load failed:', err);
                });
        },

        /**
         * Initialize period buttons
         */
        initPeriodButtons() {
            const periodButtons = document.querySelectorAll('.period-btn');
            periodButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    periodButtons.forEach(function (btn) {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                    const period = parseInt(this.getAttribute('data-period'));
                    DashboardCharts.loadAttendanceData(period);
                });
            });
        },

        buildDepartmentColorVariations(count) {
            const colors = DashboardUtils.getThemeColors();
            const baseColor = colors.primaryColor || '#012445';
            const variations = [];

            for (let i = 0; i < count; i++) {
                variations.push(DashboardUtils.lightenColor(baseColor, Math.min(i * 0.12, 0.65)));
            }

            return variations.length ? variations : [baseColor];
        },

        mapDepartmentDatasets(datasets) {
            const colorVariations = this.buildDepartmentColorVariations(datasets.length);

            return datasets.map(function (dataset, index) {
                const color = colorVariations[index % colorVariations.length];

                return {
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: color,
                    backgroundColor: DashboardUtils.hexToRgba(color, 0.3),
                    borderWidth: 3,
                    fill: true,
                    tension: 0.5,
                    pointRadius: 0,
                    pointHoverRadius: 0
                };
            });
        },

        loadDepartmentDistributionData() {
            if (!this.departmentChart) return;

            const url = (window._dashRoutes && window._dashRoutes.departmentDistribution) ?
                window._dashRoutes.departmentDistribution :
                '/admin/dashboard/department-distribution';

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function (res) {
                    return res.json();
                })
                .then(function (json) {
                    if (!json.success) return;

                    const data = json.data || {};
                    DashboardCharts.departmentChart.data.labels = data.labels || [];
                    DashboardCharts.departmentChart.data.datasets = DashboardCharts.mapDepartmentDatasets(data.datasets || []);
                    DashboardCharts.departmentChart.update();
                    DashboardCharts.createCustomLegend();
                })
                .catch(function (err) {
                    console.error('Department distribution load failed:', err);
                });
        },

        /**
         * Initialize Department Distribution Chart
         */
        initDepartmentChart() {
            const ctx = document.getElementById('departmentChart');
            if (!ctx) return;

            const colors = DashboardUtils.getThemeColors();

            this.departmentChart = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeInOutQuart'
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.85)',
                            padding: {
                                top: 16,
                                right: 18,
                                bottom: 16,
                                left: 18
                            },
                            titleFont: {
                                size: 14,
                                weight: '600',
                                family: "'Rubik', sans-serif"
                            },
                            bodyFont: {
                                size: 13,
                                weight: '500',
                                family: "'Rubik', sans-serif"
                            },
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: colors.primaryColor,
                            borderWidth: 2,
                            cornerRadius: 8,
                            displayColors: true,
                            itemSpacing: 10,
                            titleSpacing: 8,
                            bodySpacing: 8,
                            callbacks: {
                                title: function (context) {
                                    return context[0].label;
                                },
                                label: function (context) {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
                                },
                                labelColor: function (context) {
                                    return {
                                        borderColor: context.dataset.borderColor,
                                        backgroundColor: context.dataset.borderColor,
                                        borderWidth: 3,
                                        borderRadius: 2
                                    };
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            display: false,
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });

            this.loadDepartmentDistributionData();
        },

        /**
         * Create custom legend for department chart
         */
        createCustomLegend() {
            const legendContainer = document.getElementById('departmentCustomLegend');
            if (!legendContainer || !this.departmentChart) return;

            legendContainer.innerHTML = '';

            this.departmentChart.data.datasets.forEach((dataset) => {
                const li = document.createElement('li');
                li.className = 'custom-legend-item';

                const colorBox = document.createElement('span');
                colorBox.className = 'custom-legend-color';
                colorBox.style.backgroundColor = dataset.borderColor;

                const label = document.createElement('span');
                const lastValue = dataset.data[dataset.data.length - 1];
                label.textContent = `${dataset.label}: ${lastValue}%`;

                li.appendChild(colorBox);
                li.appendChild(label);
                legendContainer.appendChild(li);
            });
        },

        /**
         * Calculate and update workforce strength
         */
        updateWorkforceStrength() {
            const stats = window._dashStats || {};
            const percentage = stats.workforcePercent !== undefined ?
                stats.workforcePercent :
                0;
            const activeEmployees = stats.activeEmployees !== undefined ?
                stats.activeEmployees :
                0;
            const totalEmployees = stats.totalEmployees !== undefined ?
                stats.totalEmployees :
                0;

            const percentageEl = document.getElementById('workforcePercentage');
            const subtextEl = document.getElementById('workforceSubtext');
            const progressBarEl = document.getElementById('workforceProgressBar');

            if (percentageEl) percentageEl.textContent = percentage + '%';
            if (subtextEl) subtextEl.textContent = activeEmployees + ' Active / ' + totalEmployees + ' Total.';
            if (progressBarEl) progressBarEl.style.width = percentage + '%';
        }
    };

    // ============================================
    // APPROVALS
    // ============================================
    const DashboardApprovals = {
        currentLeaveId: null,
        currentLeaveCanAct: false,
        canActOnApprovals: true,
        isHumanResourceViewer: false,
        pendingItemsById: {},

        ensureSwalModalFocusFix() {
            if (this._swalFocusFixBound) return;
            this._swalFocusFixBound = true;
            document.addEventListener('focusin', function (e) {
                if (e.target.closest && e.target.closest('.swal2-container')) {
                    e.stopImmediatePropagation();
                }
            }, true);
        },

        showHrViewOnlyAlert() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'View Only — Human Resource',
                    html: 'As a <strong>Human Resource</strong> team member, you can monitor pending leave requests across your SBU.<br><br>Approval and rejection must be handled by the assigned <strong>manager</strong> or <strong>HOD</strong>.',
                    confirmButtonColor: '#1a237e',
                    confirmButtonText: 'Understood'
                });
                return;
            }

            alert('As a Human Resource team member, you can view leave requests but cannot approve or reject them.');
        },

        guardLeaveAction(canAct) {
            if (canAct !== false) {
                return true;
            }

            this.showHrViewOnlyAlert();
            return false;
        },

        showLeaveAlert(message, type) {
            if (type === 'success' && typeof window.showSuccess === 'function') {
                window.showSuccess(message, 'Success');
                return;
            }
            if (type === 'error' && typeof window.showError === 'function') {
                window.showError(message);
                return;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type === 'success' ? 'success' : (type === 'error' ? 'error' : 'info'),
                    title: type === 'success' ? 'Success' : (type === 'error' ? 'Error' : 'Notice'),
                    text: message,
                    timer: type === 'success' ? 1800 : undefined,
                    showConfirmButton: type !== 'success',
                    confirmButtonColor: '#1a237e'
                });
                return;
            }
            alert(message);
        },

        confirmHrDelegatedAction(actionLabel, approverName, onConfirmed) {
            const safeApproverName = approverName || 'the assigned approver';

            if (typeof Swal === 'undefined') {
                if (confirm('The real approver is ' + safeApproverName + '. Proceed on their behalf?')) {
                    onConfirmed();
                }
                return;
            }

            Swal.fire({
                icon: 'question',
                title: actionLabel + ' on Behalf?',
                html: '<p class="mb-2">The real approver for this leave request is <strong>' +
                    this.esc(safeApproverName) +
                    '</strong>.</p>' +
                    '<p class="mb-2">Do you want to ' + actionLabel.toLowerCase() +
                    ' this request on their behalf?</p>' +
                    '<p class="text-muted small mb-0">Any action you take will inform that employee through email and notification.</p>',
                showCancelButton: true,
                confirmButtonColor: '#1a237e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, ' + actionLabel,
                cancelButtonText: 'No',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    onConfirmed();
                }
            });
        },

        confirmApprove(id, onConfirmed, canAct) {
            if (!this.guardLeaveAction(canAct)) {
                return;
            }

            const item = this.pendingItemsById[id];
            if (item && item.requires_hr_delegation_confirm) {
                this.confirmHrDelegatedAction('Approve', item.assigned_approver_name, onConfirmed);
                return;
            }

            if (typeof Swal === 'undefined') {
                if (confirm('Approve this leave request?')) {
                    onConfirmed();
                }
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Approve Leave Request?',
                text: 'Are you sure you want to approve this leave request?',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    onConfirmed();
                }
            });
        },

        confirmReject(id, onConfirmed, canAct) {
            if (!this.guardLeaveAction(canAct)) {
                return;
            }

            const item = this.pendingItemsById[id];
            if (item && item.requires_hr_delegation_confirm) {
                this.confirmHrDelegatedAction('Reject', item.assigned_approver_name, onConfirmed);
                return;
            }

            if (typeof Swal === 'undefined') {
                if (confirm('Reject this leave request?')) {
                    onConfirmed();
                }
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Reject Leave Request?',
                text: 'Are you sure you want to reject this leave request?',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    onConfirmed();
                }
            });
        },

        confirmBulkApprove(count, onConfirmed) {
            const hasDelegatedItems = Array.from(document.querySelectorAll('.approval-checkbox:checked'))
                .some(function (cb) {
                    const item = DashboardApprovals.pendingItemsById[cb.value];
                    return item && item.requires_hr_delegation_confirm;
                });

            if (hasDelegatedItems) {
                this.showLeaveAlert(
                    'Bulk approve is not available for other department leave requests. Please approve them individually.',
                    'error'
                );
                return;
            }

            if (!this.guardLeaveAction()) {
                return;
            }

            if (typeof Swal === 'undefined') {
                if (confirm('Approve ' + count + ' leave request(s)?')) {
                    onConfirmed();
                }
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Bulk Approve Leave Requests?',
                text: 'Approve ' + count + ' selected leave request(s)?',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve All',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    onConfirmed();
                }
            });
        },

        loadPendingApprovals() {
            const url = (window._dashRoutes && window._dashRoutes.pendingApprovals) ?
                window._dashRoutes.pendingApprovals :
                '/admin/dashboard/pending-approvals';

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function (res) {
                    return res.json();
                })
                .then(function (json) {
                    const loader = document.getElementById('approvalsLoader');
                    if (loader) loader.remove();
                    if (!json.success) return;
                    DashboardApprovals.canActOnApprovals = json.can_act_on_approvals !== false;
                    DashboardApprovals.isHumanResourceViewer = json.is_human_resource_viewer === true;
                    DashboardApprovals.renderApprovals(json.data);
                    DashboardApprovals.updatePendingCount();
                    DashboardApprovals.initBulkApprove();
                })
                .catch(function (err) {
                    console.error('Pending approvals load failed:', err);
                    const loader = document.getElementById('approvalsLoader');
                    if (loader) loader.innerHTML = '<span class="text-danger small px-4">Failed to load.</span>';
                });
        },

        renderApprovals(items) {
            const list = document.getElementById('approvalsList');
            if (!list) return;
            list.innerHTML = '';
            this.pendingItemsById = {};

            const header = document.getElementById('bulkApproveHeader');
            if (header) {
                header.style.display = DashboardApprovals.canActOnApprovals ? '' : 'none';
            }

            if (!items || items.length === 0) {
                const empty = document.getElementById('pendingApprovalsEmpty');
                if (empty) empty.classList.remove('d-none');
                return;
            }

            items.forEach(function (item) {
                DashboardApprovals.pendingItemsById[item.id] = item;
                const itemCanAct = item.can_act === true;
                const div = document.createElement('div');
                div.className = 'approval-item';
                div.setAttribute('data-approval-id', item.id);
                div.setAttribute('data-can-act', itemCanAct ? '1' : '0');

                const rowCheckboxHtml = itemCanAct
                    ? '<input type="checkbox" class="form-check-input approval-checkbox approval-item-checkbox" value="' + item.id + '">'
                    : '';

                const actionButtonsHtml = itemCanAct
                    ? '<button class="btn btn-sm btn-success bg-main rounded-3 border-0 approve-btn" data-id="' + item.id + '" data-can-act="1" title="Approve">' +
                    '<i class="bi bi-check-lg"></i>' +
                    '</button>' +
                    '<button class="btn btn-sm btn-danger border-0 bg-main rounded-3 reject-btn" data-id="' + item.id + '" data-can-act="1" title="Reject">' +
                    '<i class="bi bi-x-lg"></i>' +
                    '</button>'
                    : '<button class="btn btn-sm btn-outline-success rounded-3 border approve-btn" data-id="' + item.id + '" data-can-act="0" title="Approve">' +
                    '<i class="bi bi-check-lg"></i>' +
                    '</button>' +
                    '<button class="btn btn-sm btn-outline-danger rounded-3 border reject-btn" data-id="' + item.id + '" data-can-act="0" title="Reject">' +
                    '<i class="bi bi-x-lg"></i>' +
                    '</button>';

                div.innerHTML =
                    '<div class="d-flex align-items-center justify-content-between">' +
                    '<div class="d-flex align-items-center flex-grow-1">' +
                    rowCheckboxHtml +
                    '<div class="employee-avatar me-2">' + DashboardApprovals.esc(item.initials) + '</div>' +
                    '<div class="flex-grow-1">' +
                    '<h6 class="mb-0 small">' + DashboardApprovals.esc(item.name) + '</h6>' +
                    '<small class="text-muted">' + DashboardApprovals.esc(item.leave_type) + '</small>' +
                    '<div class="small text-muted">Requested by: ' + DashboardApprovals.esc(item.requested_by) + '</div>' +
                    '<div class="small text-muted">Requested: ' + DashboardApprovals.esc(item.request_date) + '</div>' +
                    (DashboardApprovals.isHumanResourceViewer && !itemCanAct
                        ? '<div class="small text-info"><i class="bi bi-eye me-1"></i>View only</div>'
                        : '') +
                    '</div>' +
                    '</div>' +
                    '<div class="d-flex gap-2">' +
                    '<button class="btn btn-sm btn-outline-secondary rounded-3 border view-btn" title="View Reason"' +
                    ' data-id="' + item.id + '"' +
                    ' data-name="' + DashboardApprovals.esc(item.name) + '"' +
                    ' data-initials="' + DashboardApprovals.esc(item.initials) + '"' +
                    ' data-leave-type="' + DashboardApprovals.esc(item.leave_type) + '"' +
                    ' data-request-date="' + DashboardApprovals.esc(item.request_date) + '"' +
                    ' data-requested-by="' + DashboardApprovals.esc(item.requested_by) + '"' +
                    ' data-start-date="' + DashboardApprovals.esc(item.start_date) + '"' +
                    ' data-end-date="' + DashboardApprovals.esc(item.end_date) + '"' +
                    ' data-reason="' + DashboardApprovals.esc(item.reason) + '">' +
                    '<i class="bi bi-eye"></i>' +
                    '</button>' +
                    actionButtonsHtml +
                    '</div>' +
                    '</div>';
                list.appendChild(div);
            });

            DashboardApprovals.initApprovalButtons();
        },

        esc(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        },

        initBulkApprove() {
            const bulkApproveAll = document.getElementById('bulkApproveAll');
            if (!bulkApproveAll) return;

            bulkApproveAll.onchange = function () {
                document.querySelectorAll('.approval-checkbox').forEach(function (cb) {
                    cb.checked = bulkApproveAll.checked;
                });
                DashboardApprovals.updateBulkApproveButton();
            };

            const bulkApproveBtn = document.getElementById('bulkApproveBtn');
            if (bulkApproveBtn) {
                bulkApproveBtn.onclick = function () {
                    const ids = Array.from(document.querySelectorAll('.approval-checkbox:checked')).map(function (cb) {
                        return cb.value;
                    });
                    if (ids.length === 0) return;
                    if (ids.length > 10) {
                        DashboardApprovals.showLeaveAlert('You can only approve up to 10 requests at once.', 'error');
                        return;
                    }
                    DashboardApprovals.confirmBulkApprove(ids.length, function () {
                        DashboardApprovals.bulkApproveApi(ids);
                    });
                };
            }
        },

        updateBulkApproveButton() {
            const checked = document.querySelectorAll('.approval-checkbox:checked').length;
            const bulkBtn = document.getElementById('bulkApproveBtn');
            const countSpan = document.getElementById('selectedCount');
            if (checked > 0) {
                if (bulkBtn) {
                    bulkBtn.classList.remove('d-none');
                    bulkBtn.disabled = false;
                }
                if (countSpan) countSpan.textContent = checked;
            } else {
                if (bulkBtn) {
                    bulkBtn.classList.add('d-none');
                    bulkBtn.disabled = true;
                }
            }
        },

        updateSelectAllCheckbox() {
            const total = document.querySelectorAll('.approval-checkbox').length;
            const checked = document.querySelectorAll('.approval-checkbox:checked').length;
            const selectAll = document.getElementById('bulkApproveAll');
            if (!selectAll) return;
            if (checked === 0) {
                selectAll.indeterminate = false;
                selectAll.checked = false;
            } else if (checked === total) {
                selectAll.indeterminate = false;
                selectAll.checked = true;
            } else {
                selectAll.indeterminate = true;
            }
        },

        initApprovalButtons() {
            document.querySelectorAll('.approve-btn').forEach(function (btn) {
                btn.onclick = function () {
                    const id = this.getAttribute('data-id');
                    const canAct = this.getAttribute('data-can-act') === '1';
                    DashboardApprovals.confirmApprove(id, function () {
                        DashboardApprovals.performStatusUpdate(id, 3, { canAct: true });
                    }, canAct);
                };
            });

            document.querySelectorAll('.reject-btn').forEach(function (btn) {
                btn.onclick = function () {
                    const id = this.getAttribute('data-id');
                    const canAct = this.getAttribute('data-can-act') === '1';
                    DashboardApprovals.confirmReject(id, function () {
                        DashboardApprovals.performStatusUpdate(id, 4, { canAct: true });
                    }, canAct);
                };
            });

            document.querySelectorAll('.view-btn').forEach(function (btn) {
                btn.onclick = function () {
                    const itemEl = this.closest('.approval-item');
                    const canAct = itemEl ? itemEl.getAttribute('data-can-act') === '1' : false;
                    DashboardApprovals.viewLeaveReason(
                        this.getAttribute('data-id'),
                        this.getAttribute('data-name'),
                        this.getAttribute('data-initials'),
                        this.getAttribute('data-leave-type'),
                        this.getAttribute('data-request-date'),
                        this.getAttribute('data-start-date'),
                        this.getAttribute('data-end-date'),
                        this.getAttribute('data-reason'),
                        this.getAttribute('data-requested-by'),
                        canAct
                    );
                };
            });

            document.querySelectorAll('.approval-checkbox').forEach(function (cb) {
                cb.onchange = function () {
                    DashboardApprovals.updateBulkApproveButton();
                    DashboardApprovals.updateSelectAllCheckbox();
                };
            });
        },

        performStatusUpdate(id, status, options) {
            options = options || {};

            if (options.canAct === false) {
                this.showHrViewOnlyAlert();
                return Promise.resolve({ success: false, message: 'View only access.' });
            }

            const baseUrl = (window._dashRoutes && window._dashRoutes.leaveRequestStatus) ?
                window._dashRoutes.leaveRequestStatus :
                '/admin/leave-request/{id}/status';
            const url = baseUrl.replace('{id}', id);
            const csrf = window._csrfToken || '';

            return fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: status
                    })
                })
                .then(function (res) {
                    return res.json().then(function (json) {
                        return { ok: res.ok, json: json };
                    });
                })
                .then(function (result) {
                    const json = result.json || {};
                    if (result.ok && json.success !== false) {
                        DashboardApprovals.removeItem(id);
                        if (!options.silent) {
                            DashboardApprovals.showLeaveAlert(
                                json.message || (status === 3
                                    ? 'Leave request approved successfully.'
                                    : 'Leave request rejected successfully.'),
                                'success'
                            );
                        }
                        return { success: true, message: json.message || '' };
                    }

                    const message = json.message || 'Action failed.';
                    if (!options.silent) {
                        DashboardApprovals.showLeaveAlert(message, 'error');
                    }
                    return { success: false, message: message };
                })
                .catch(function (err) {
                    console.error('Status update failed:', err);
                    const message = 'Action failed. Please try again.';
                    if (!options.silent) {
                        DashboardApprovals.showLeaveAlert(message, 'error');
                    }
                    return { success: false, message: message };
                });
        },

        bulkApproveApi(ids) {
            const bulkApproveBtn = document.getElementById('bulkApproveBtn');
            if (bulkApproveBtn) bulkApproveBtn.disabled = true;

            Promise.all(ids.map(function (id) {
                return DashboardApprovals.performStatusUpdate(id, 3, { silent: true });
            })).then(function (results) {
                const succeeded = results.filter(function (result) { return result.success; }).length;
                const failed = results.length - succeeded;

                if (failed === 0) {
                    DashboardApprovals.showLeaveAlert(
                        succeeded === 1
                            ? 'Leave request approved successfully.'
                            : succeeded + ' leave requests approved successfully.',
                        'success'
                    );
                } else if (succeeded === 0) {
                    DashboardApprovals.showLeaveAlert('Failed to approve leave requests.', 'error');
                } else {
                    DashboardApprovals.showLeaveAlert(
                        succeeded + ' approved, ' + failed + ' failed.',
                        'error'
                    );
                }
            }).finally(function () {
                const bulkApproveAll = document.getElementById('bulkApproveAll');
                if (bulkApproveAll) bulkApproveAll.checked = false;
                DashboardApprovals.updateBulkApproveButton();
                if (bulkApproveBtn) bulkApproveBtn.disabled = false;
            });
        },

        removeItem(id) {
            const item = document.querySelector('.approval-item[data-approval-id="' + id + '"]');
            if (item) {
                item.style.transition = 'opacity 0.3s ease';
                item.style.opacity = '0';
                setTimeout(function () {
                    item.remove();
                    DashboardApprovals.updatePendingCount();
                }, 300);
            }
        },

        updatePendingCount() {
            const items = document.querySelectorAll('.approval-item').length;
            const badge = document.getElementById('pendingApprovalsBadge');
            const empty = document.getElementById('pendingApprovalsEmpty');
            const header = document.getElementById('bulkApproveHeader');

            if (badge) badge.textContent = items;

            if (items === 0) {
                if (empty) empty.classList.remove('d-none');
                if (header) header.style.display = 'none';
            } else {
                if (empty) empty.classList.add('d-none');
                if (header) {
                    header.style.display = DashboardApprovals.canActOnApprovals ? '' : 'none';
                }
            }
        },

        viewLeaveReason(id, name, initials, leaveType, requestDate, startDate, endDate, reason, requestedBy, canAct) {
            this.currentLeaveId = id;
            this.currentLeaveCanAct = canAct === true;
            var avatarEl = document.getElementById('slideEmployeeAvatar');
            var nameEl = document.getElementById('slideEmployeeName');
            var typeEl = document.getElementById('slideLeaveType');
            var requestedByEl = document.getElementById('slideRequestedBy');
            var requestDateEl = document.getElementById('slideRequestDate');
            var startDateEl = document.getElementById('slideStartDate');
            var endDateEl = document.getElementById('slideEndDate');
            var reasonEl = document.getElementById('slideReason');

            if (avatarEl) avatarEl.textContent = initials || '';
            if (nameEl) nameEl.textContent = name || '';
            if (typeEl) typeEl.textContent = leaveType || '';
            if (requestedByEl) requestedByEl.textContent = requestedBy || '-';
            if (requestDateEl) requestDateEl.textContent = requestDate || '';
            if (startDateEl) startDateEl.textContent = startDate || '';
            if (endDateEl) endDateEl.textContent = endDate || '';
            if (reasonEl) reasonEl.textContent = reason || '';

            var backdrop = document.getElementById('slideOverBackdrop');
            var panel = document.getElementById('slideOverPanel');
            var slideApproveBtn = document.getElementById('slideApproveBtn');
            var slideRejectBtn = document.getElementById('slideRejectBtn');
            if (slideApproveBtn) slideApproveBtn.style.display = this.currentLeaveCanAct ? '' : 'none';
            if (slideRejectBtn) slideRejectBtn.style.display = this.currentLeaveCanAct ? '' : 'none';

            if (backdrop) backdrop.classList.add('show');
            if (panel) panel.classList.add('show');
            document.body.style.overflow = '';
        },

        closeSlideOver() {
            var backdrop = document.getElementById('slideOverBackdrop');
            var panel = document.getElementById('slideOverPanel');
            if (backdrop) backdrop.classList.remove('show');
            if (panel) panel.classList.remove('show');
            document.body.style.overflow = '';
            this.currentLeaveId = null;
            this.currentLeaveCanAct = false;
        },

        approveFromSlide() {
            if (!this.currentLeaveId) return;
            const leaveId = this.currentLeaveId;
            const canAct = this.currentLeaveCanAct;
            this.confirmApprove(leaveId, function () {
                DashboardApprovals.performStatusUpdate(leaveId, 3, { canAct: true }).then(function (result) {
                    if (result.success) {
                        DashboardApprovals.closeSlideOver();
                    }
                });
            }, canAct);
        },

        rejectFromSlide() {
            if (!this.currentLeaveId) return;
            const leaveId = this.currentLeaveId;
            const canAct = this.currentLeaveCanAct;
            this.confirmReject(leaveId, function () {
                DashboardApprovals.performStatusUpdate(leaveId, 4, { canAct: true }).then(function (result) {
                    if (result.success) {
                        DashboardApprovals.closeSlideOver();
                    }
                });
            }, canAct);
        },

        initSlideOverKeyboard() {
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    DashboardApprovals.closeSlideOver();
                }
            });
        }
    };

    // ============================================
    // ROSTER APPROVALS
    // ============================================
    const DashboardRosterApprovals = {
        currentRequestId: null,
        currentRequestData: null,

        loadPendingRosterApprovals() {
            var url = (window._dashRoutes && window._dashRoutes.pendingRosterApprovals)
                ? window._dashRoutes.pendingRosterApprovals
                : '/admin/dashboard/pending-roster-approvals';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    var loader = document.getElementById('rosterApprovalsLoader');
                    if (loader) loader.remove();
                    if (!json.success) return;
                    DashboardRosterApprovals.renderApprovals(json.data || []);
                    DashboardRosterApprovals.updateBadge(json.count || 0);
                })
                .catch(function (err) {
                    console.error('Pending roster approvals load failed:', err);
                    var loader = document.getElementById('rosterApprovalsLoader');
                    if (loader) loader.innerHTML = '<span class="text-danger small px-4">Failed to load.</span>';
                });
        },

        renderApprovals(items) {
            var list = document.getElementById('rosterApprovalsList');
            if (!list) return;
            list.innerHTML = '';

            if (!items || items.length === 0) {
                var empty = document.getElementById('pendingRosterApprovalsEmpty');
                if (empty) empty.classList.remove('d-none');
                return;
            }

            var empty = document.getElementById('pendingRosterApprovalsEmpty');
            if (empty) empty.classList.add('d-none');

            items.forEach(function (item) {
                var div = document.createElement('div');
                div.className = 'approval-item roster-approval-item';
                div.innerHTML =
                    '<div class="d-flex align-items-center justify-content-between gap-2">' +
                    '<div class="d-flex align-items-center flex-grow-1">' +
                    '<div class="employee-avatar me-2">' + DashboardApprovals.esc(item.initials) + '</div>' +
                    '<div class="flex-grow-1">' +
                    '<h6 class="mb-0 small">' + DashboardApprovals.esc(item.name) + '</h6>' +
                    '<small class="text-muted">' + DashboardApprovals.esc(item.shift_label) + '</small>' +
                    '<div class="small text-muted">' + DashboardApprovals.esc(item.duration_label) + '</div>' +
                    '<div class="small text-muted">By ' + DashboardApprovals.esc(item.requested_by) + ' • ' + DashboardApprovals.esc(item.request_date) + '</div>' +
                    '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary rounded-3 border roster-view-btn" data-id="' + item.id + '">' +
                    '<i class="bi bi-eye me-1"></i>View</button>' +
                    '</div>';
                list.appendChild(div);
            });

            list.querySelectorAll('.roster-view-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var requestId = parseInt(btn.getAttribute('data-id'), 10);
                    var plannerUrl = (window._dashRoutes && window._dashRoutes.shiftPlannerUrl)
                        ? window._dashRoutes.shiftPlannerUrl
                        : '/admin/shift-planner';
                    sessionStorage.setItem('rosterApprovalReviewId', String(requestId));
                    window.location.href = plannerUrl;
                });
            });
        },

        updateBadge(count) {
            var badge = document.getElementById('pendingRosterApprovalsBadge');
            if (badge) badge.textContent = String(count || 0);
        },

        openModal(requestId) {
            this.currentRequestId = requestId;
            var modalEl = document.getElementById('rosterApprovalModal');
            if (!modalEl || typeof bootstrap === 'undefined') return;

            var loader = document.getElementById('rosterApprovalModalLoader');
            var content = document.getElementById('rosterApprovalModalContent');
            if (loader) loader.classList.remove('d-none');
            if (content) content.classList.add('d-none');

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            this.loadDetail(requestId);
        },

        loadDetail(requestId) {
            var baseUrl = (window._dashRoutes && window._dashRoutes.rosterApprovalShow)
                ? window._dashRoutes.rosterApprovalShow
                : '/admin/shift-roster/approvals';

            fetch(baseUrl + '/' + requestId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (!json.success || !json.data) {
                        throw new Error(json.message || 'Could not load roster request.');
                    }
                    DashboardRosterApprovals.renderDetail(json.data);
                })
                .catch(function (err) {
                    DashboardRosterApprovals.showRosterAlert(err.message || 'Could not load roster request.', 'error');
                });
        },

        renderDetail(data) {
            this.currentRequestData = data || null;
            var loader = document.getElementById('rosterApprovalModalLoader');
            var content = document.getElementById('rosterApprovalModalContent');
            if (loader) loader.classList.add('d-none');
            if (content) content.classList.remove('d-none');

            var avatar = document.getElementById('rosterApprovalAvatar');
            if (avatar) avatar.textContent = data.assignee_initials || '--';

            document.getElementById('rosterApprovalAssignee').textContent = data.assignee_name || '-';
            document.getElementById('rosterApprovalDepartment').textContent = data.department || '-';
            document.getElementById('rosterApprovalRequestedBy').textContent = data.requested_by || '-';
            document.getElementById('rosterApprovalShiftLabel').textContent = data.shift_label || '-';
            document.getElementById('rosterApprovalPeriod').textContent = data.period_label
                || ((data.start_date || '-') + ' – ' + (data.end_date || '-'));
            document.getElementById('rosterApprovalDuration').textContent = data.duration_label || '-';

            this.renderStepper(data.approval_status || 'pending');

            var items = data.items || [];
            var totalItems = data.total_items || items.length;
            var previewCount = document.getElementById('rosterApprovalPreviewCount');
            if (previewCount) {
                previewCount.textContent = totalItems + (totalItems === 1 ? ' day' : ' days');
            }

            var tbody = document.getElementById('rosterApprovalItemsBody');
            if (!tbody) return;
            tbody.innerHTML = '';

            items.forEach(function (item) {
                var tr = document.createElement('tr');

                var typeBadgeClass = item.entry_type === 'off' || item.entry_type === 'delete'
                    ? 'roster-approval-type-badge roster-approval-type-badge--off'
                    : 'roster-approval-type-badge';
                var typeLabel = item.entry_type === 'delete'
                    ? 'Remove'
                    : (item.entry_type === 'off' ? 'Off' : (item.shift_name || 'Shift'));

                var timeLabel = item.entry_type === 'off' || item.entry_type === 'delete'
                    ? '-'
                    : ((item.start_time || '-') + ' – ' + (item.end_time || '-'));

                tr.innerHTML =
                    '<td>' + DashboardApprovals.esc(item.date) + '</td>' +
                    '<td><span class="' + typeBadgeClass + '">' + DashboardApprovals.esc(typeLabel) + '</span></td>' +
                    '<td>' + DashboardApprovals.esc(timeLabel) + '</td>';
                tbody.appendChild(tr);
            });

            var actionButtons = document.getElementById('rosterApprovalActionButtons');
            if (actionButtons) {
                actionButtons.classList.toggle('d-none', (data.approval_status || 'pending') !== 'pending');
            }
        },

        renderStepper(status) {
            var stepper = document.getElementById('rosterApprovalStepper');
            if (!stepper) return;

            var submitted = stepper.querySelector('[data-step="submitted"]');
            var review = stepper.querySelector('[data-step="review"]');
            var approved = stepper.querySelector('[data-step="approved"]');
            if (!submitted || !review || !approved) return;

            var resetStep = function (el, baseClass) {
                el.className = 'roster-approval-step ' + baseClass;
            };

            resetStep(submitted, 'roster-approval-step--done');
            resetStep(review, 'roster-approval-step--awaiting');
            resetStep(approved, 'roster-approval-step--awaiting');

            submitted.querySelector('.roster-approval-step__status').textContent = 'Done';

            if (status === 'approved') {
                resetStep(review, 'roster-approval-step--done');
                resetStep(approved, 'roster-approval-step--done');
                review.querySelector('.roster-approval-step__status').textContent = 'Done';
                approved.querySelector('.roster-approval-step__status').textContent = 'Done';
                return;
            }

            if (status === 'rejected') {
                resetStep(review, 'roster-approval-step--rejected');
                review.querySelector('.roster-approval-step__status').textContent = 'Rejected';
                approved.querySelector('.roster-approval-step__status').textContent = 'Cancelled';
                return;
            }

            resetStep(review, 'roster-approval-step--active');
            review.querySelector('.roster-approval-step__status').textContent = 'Pending';
            approved.querySelector('.roster-approval-step__status').textContent = 'Awaiting';
        },

        ensureSwalModalFocusFix() {
            if (this._swalFocusFixBound) return;
            this._swalFocusFixBound = true;
            document.addEventListener('focusin', function (e) {
                if (e.target.closest && e.target.closest('.swal2-container')) {
                    e.stopImmediatePropagation();
                }
            }, true);
        },

        showRosterAlert(message, type) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type === 'success' ? 'success' : (type === 'error' ? 'error' : 'info'),
                    title: type === 'success' ? 'Success' : (type === 'error' ? 'Error' : 'Notice'),
                    text: message,
                    timer: type === 'success' ? 1800 : undefined,
                    showConfirmButton: type !== 'success'
                });
                return;
            }

            alert(message);
        },

        performApprove() {
            if (!this.currentRequestId) return;

            var baseUrl = (window._dashRoutes && window._dashRoutes.rosterApprovalApprove)
                ? window._dashRoutes.rosterApprovalApprove
                : '/admin/shift-roster/approvals';
            var approveBtn = document.getElementById('rosterApprovalApproveBtn');
            if (approveBtn) approveBtn.disabled = true;

            fetch(baseUrl + '/' + this.currentRequestId + '/approve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window._csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
                .then(function (res) { return res.json().then(function (body) { return { ok: res.ok, body: body }; }); })
                .then(function (result) {
                    if (!result.ok || !result.body.success) {
                        throw new Error(result.body.message || 'Approval failed.');
                    }
                    DashboardRosterApprovals.showRosterAlert(result.body.message || 'Roster approved.', 'success');
                    DashboardRosterApprovals.closeModal();
                    DashboardRosterApprovals.loadPendingRosterApprovals();
                })
                .catch(function (err) {
                    DashboardRosterApprovals.showRosterAlert(err.message || 'Approval failed.', 'error');
                })
                .finally(function () {
                    if (approveBtn) approveBtn.disabled = false;
                });
        },

        performReject(reason) {
            if (!this.currentRequestId) return;

            var baseUrl = (window._dashRoutes && window._dashRoutes.rosterApprovalReject)
                ? window._dashRoutes.rosterApprovalReject
                : '/admin/shift-roster/approvals';
            var rejectBtn = document.getElementById('rosterApprovalRejectBtn');
            if (rejectBtn) rejectBtn.disabled = true;

            fetch(baseUrl + '/' + this.currentRequestId + '/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window._csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ reason: reason || '' })
            })
                .then(function (res) { return res.json().then(function (body) { return { ok: res.ok, body: body }; }); })
                .then(function (result) {
                    if (!result.ok || !result.body.success) {
                        throw new Error(result.body.message || 'Reject failed.');
                    }
                    DashboardRosterApprovals.showRosterAlert(result.body.message || 'Roster rejected.', 'success');
                    DashboardRosterApprovals.closeModal();
                    DashboardRosterApprovals.loadPendingRosterApprovals();
                })
                .catch(function (err) {
                    DashboardRosterApprovals.showRosterAlert(err.message || 'Reject failed.', 'error');
                })
                .finally(function () {
                    if (rejectBtn) rejectBtn.disabled = false;
                });
        },

        getApproveConfirmCopy(data) {
            data = data || this.currentRequestData || {};
            var requestType = data.request_type || '';
            var items = data.items || [];
            var hasDelete = items.some(function (item) { return item.entry_type === 'delete'; });
            var hasShift = items.some(function (item) { return item.entry_type === 'shift'; });
            var hasOff = items.some(function (item) { return item.entry_type === 'off'; });

            if (requestType === 'delete' || (hasDelete && !hasShift && !hasOff)) {
                return {
                    title: 'Approve removal?',
                    text: items.length > 1
                        ? 'This will remove the submitted shifts from the employee roster.'
                        : 'This will remove the submitted shift from the employee roster.'
                };
            }

            if (requestType === 'update') {
                return {
                    title: 'Approve update?',
                    text: 'This will apply the submitted roster changes to the employee.'
                };
            }

            if (hasOff && !hasShift) {
                return {
                    title: 'Approve roster?',
                    text: 'This will mark the submitted off day(s) on the employee roster.'
                };
            }

            return {
                title: 'Approve roster?',
                text: 'This will assign the submitted shifts to the employee.'
            };
        },

        approveCurrent() {
            if (!this.currentRequestId) return;

            var confirmCopy = this.getApproveConfirmCopy();

            if (typeof Swal === 'undefined') {
                if (!confirm(confirmCopy.text)) return;
                this.performApprove();
                return;
            }

            Swal.fire({
                title: confirmCopy.title,
                text: confirmCopy.text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, approve',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    DashboardRosterApprovals.performApprove();
                }
            });
        },

        rejectCurrent() {
            if (!this.currentRequestId) return;

            if (typeof Swal === 'undefined') {
                if (!confirm('Reject this shift roster request?')) return;
                this.performReject('');
                return;
            }

            Swal.fire({
                title: 'Reject roster?',
                text: 'The submitted roster will not be applied.',
                icon: 'warning',
                input: 'textarea',
                inputPlaceholder: 'Optional rejection reason...',
                inputAttributes: {
                    maxlength: 1000,
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, reject',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                didOpen: function () {
                    var input = Swal.getInput();
                    if (input) {
                        input.removeAttribute('readonly');
                        input.removeAttribute('disabled');
                        input.focus();
                    }
                }
            }).then(function (result) {
                if (result.isConfirmed) {
                    DashboardRosterApprovals.performReject(result.value || '');
                }
            });
        },

        closeModal() {
            var modalEl = document.getElementById('rosterApprovalModal');
            if (!modalEl || typeof bootstrap === 'undefined') return;
            var instance = bootstrap.Modal.getInstance(modalEl);
            if (instance) instance.hide();
            this.currentRequestId = null;
            this.currentRequestData = null;
        },

        initButtons() {
            this.ensureSwalModalFocusFix();
            var approveBtn = document.getElementById('rosterApprovalApproveBtn');
            var rejectBtn = document.getElementById('rosterApprovalRejectBtn');
            if (approveBtn) approveBtn.addEventListener('click', function () { DashboardRosterApprovals.approveCurrent(); });
            if (rejectBtn) rejectBtn.addEventListener('click', function () { DashboardRosterApprovals.rejectCurrent(); });
        },

        openFromQueryParam() {
            var params = new URLSearchParams(window.location.search);
            var requestId = parseInt(params.get('roster_approval') || '0', 10);
            if (requestId > 0) {
                var plannerUrl = (window._dashRoutes && window._dashRoutes.shiftPlannerUrl)
                    ? window._dashRoutes.shiftPlannerUrl
                    : '/admin/shift-planner';
                sessionStorage.setItem('rosterApprovalReviewId', String(requestId));
                window.location.href = plannerUrl;
            }
        }
    };

    // ============================================
    // UPCOMING HOLIDAYS
    // ============================================
    const DashboardHolidays = {
        load(period) {
            const url = (window._dashRoutes && window._dashRoutes.upcomingHolidays) ?
                window._dashRoutes.upcomingHolidays + '?period=' + period :
                '/admin/dashboard/upcoming-holidays?period=' + period;

            const list = document.getElementById('holidaysList');
            if (list) {
                list.innerHTML = '<div class="text-center py-4 text-muted" id="holidaysLoader">' +
                    '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</div>';
            }

            const empty = document.getElementById('holidaysEmpty');
            if (empty) empty.classList.add('d-none');

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function (res) {
                    return res.json();
                })
                .then(function (json) {
                    if (!json.success) return;
                    DashboardHolidays.render(json.data);
                })
                .catch(function (err) {
                    console.error('Holidays load failed:', err);
                    const loader = document.getElementById('holidaysLoader');
                    if (loader) loader.innerHTML = '<span class="text-danger small px-4">Failed to load.</span>';
                });
        },

        render(items) {
            const list = document.getElementById('holidaysList');
            const badge = document.getElementById('holidaysBadge');
            const empty = document.getElementById('holidaysEmpty');
            if (!list) return;
            list.innerHTML = '';

            if (badge) badge.textContent = items.length;

            if (!items || items.length === 0) {
                if (empty) empty.classList.remove('d-none');
                return;
            }

            if (empty) empty.classList.add('d-none');

            items.forEach(function (item, idx) {
                var isLast = idx === items.length - 1;
                var borderCls = isLast ? '' : 'border-bottom';
                var div = document.createElement('div');
                div.className = 'holiday-item ' + borderCls + ' p-3';
                var ongoingBadge = item.is_ongoing ?
                    '<span class="badge bg-success ms-1">Ongoing</span>' :
                    '';
                div.innerHTML =
                    '<div class="d-flex align-items-center justify-content-between">' +
                    '<div class="d-flex align-items-center">' +
                    '<div class="holiday-date me-3 text-center">' +
                    '<div class="fw-bold text-main">' + DashboardHolidays.esc(item.day) + '</div>' +
                    '<small class="text-muted">' + DashboardHolidays.esc(item.month) + '</small>' +
                    '</div>' +
                    '<div>' +
                    '<h6 class="mb-0 small">' + DashboardHolidays.esc(item.name) + ongoingBadge + '</h6>' +
                    '<small class="text-muted">' + DashboardHolidays.esc(item.type) + '</small>' +
                    '</div>' +
                    '</div>' +
                    '<span class="badge ' + DashboardHolidays.esc(item.badge_class) + '">' +
                    DashboardHolidays.esc(item.scope_label) +
                    '</span>' +
                    '</div>';
                list.appendChild(div);
            });
        },

        esc(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        },

        initPeriodButtons() {
            var buttons = document.querySelectorAll('.holiday-period-btn');
            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    buttons.forEach(function (b) {
                        b.classList.remove('active', 'btn-warning');
                        b.classList.add('btn-outline-secondary');
                    });
                    this.classList.add('active', 'btn-warning');
                    this.classList.remove('btn-outline-secondary');
                    DashboardHolidays.load(parseInt(this.getAttribute('data-period')));
                });
            });
        }
    };

    // ============================================
    // EXCEPTIONS
    // ============================================
    const DashboardExceptions = {
        /**
         * Initialize notify manager buttons
         */
        initNotifyButtons() {
            document.querySelectorAll('.notify-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    DashboardExceptions.notifyManager(id);
                });
            });
        },

        /**
         * Notify Manager - sends email/ping to manager
         */
        notifyManager(exceptionId) {
            const item = document.querySelector(`.exception-item[data-exception-id="${exceptionId}"]`);
            if (!item) return;

            const employeeName = item.querySelector('h6').textContent.trim();
            const statusElement = item.querySelector('.exception-status');
            const status = statusElement ? statusElement.textContent.trim() : '';
            const scheduledTime = item.querySelector('.time-comparison .time-item:first-child .time-value').textContent.trim();
            const actualTime = item.querySelector('.time-comparison .time-item:last-child .time-value').textContent.trim();

            console.log('Notifying manager for:', {
                id: exceptionId,
                employee: employeeName,
                scheduled: scheduledTime,
                actual: actualTime,
                status: status
            });

            const btn = item.querySelector('.notify-btn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Sent';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');

            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 3000);

            DashboardUtils.showAlert(`Notification sent to ${employeeName}'s manager about ${status.toLowerCase()}.`);
        }
    };

    // ============================================
    // GEOFENCE
    // ============================================
    const DashboardGeofence = {
        map: null,

        initialize() {
            const mapElement = document.getElementById('geofenceMap');
            if (!mapElement) return;

            if (!document.querySelector('link[href*="leaflet"]')) {
                const leafletCSS = document.createElement('link');
                leafletCSS.rel = 'stylesheet';
                leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                leafletCSS.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
                leafletCSS.crossOrigin = '';
                document.head.appendChild(leafletCSS);
            }

            if (typeof L === 'undefined') {
                const leafletJS = document.createElement('script');
                leafletJS.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                leafletJS.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
                leafletJS.crossOrigin = '';
                leafletJS.onload = () => this.createMap();
                document.body.appendChild(leafletJS);
            } else {
                this.createMap();
            }
        },

        createMap() {
            const geofences = window.dashboardGeofences || [];

            this.map = L.map('geofenceMap').setView([33.5651, 73.0169], 12);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                maxZoom: 19,
                subdomains: 'abcd'
            }).addTo(this.map);

            if (!geofences.length) {
                return;
            }

            const layers = [];

            geofences.forEach(fenceData => {
                if (!fenceData.lat || !fenceData.lng) return;

                let radiusInMeters = parseFloat(fenceData.radius || 0);

                if (fenceData.radiusUnit === 'kilometers') {
                    radiusInMeters = radiusInMeters * 1000;
                }

                const color = fenceData.type === 'hard-lock' ? '#dc3545' : '#ffc107';

                const circle = L.circle([fenceData.lat, fenceData.lng], {
                    color: color,
                    fillColor: color,
                    fillOpacity: 0.2,
                    radius: radiusInMeters
                }).addTo(this.map);

                const marker = L.marker([fenceData.lat, fenceData.lng]).addTo(this.map);

                marker.bindPopup(`
                    <div class="p-2">
                        <strong class="d-block mb-1">${fenceData.name}</strong>
                        <small class="text-muted d-block mb-2">${fenceData.address}</small>
                        <div class="small">Radius: ${fenceData.radius} ${fenceData.radiusUnit}</div>
                    </div>
                `);

                layers.push(circle);
                layers.push(marker);
            });

            if (layers.length) {
                const group = new L.featureGroup(layers);
                this.map.fitBounds(group.getBounds().pad(0.1));
            }
        }
    };

    // ============================================
    // GLOBAL FUNCTIONS (for inline handlers)
    // ============================================
    window.viewLeaveReason = function (id, name, initials, leaveType, requestDate, startDate, endDate, reason, requestedBy) {
        DashboardApprovals.viewLeaveReason(id, name, initials, leaveType, requestDate, startDate, endDate, reason, requestedBy);
    };

    window.closeSlideOver = function () {
        DashboardApprovals.closeSlideOver();
    };

    window.approveFromSlide = function () {
        DashboardApprovals.approveFromSlide();
    };

    window.rejectFromSlide = function () {
        DashboardApprovals.rejectFromSlide();
    };

    // ============================================
    // INITIALIZATION
    // ============================================
    function initDashboard() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        // Initialize charts
        DashboardCharts.initAttendanceChart();
        DashboardCharts.initDepartmentChart();
        DashboardCharts.initPeriodButtons();
        DashboardCharts.updateWorkforceStrength();

        // Initialize approval management
        DashboardApprovals.ensureSwalModalFocusFix();
        DashboardApprovals.loadPendingApprovals();
        DashboardApprovals.initSlideOverKeyboard();
        DashboardRosterApprovals.loadPendingRosterApprovals();
        DashboardRosterApprovals.initButtons();
        DashboardRosterApprovals.openFromQueryParam();

        // Initialize exception management
        DashboardExceptions.initNotifyButtons();

        // Initialize upcoming holidays
        DashboardHolidays.load(7);
        DashboardHolidays.initPeriodButtons();

        // Initialize geofence map
        setTimeout(() => DashboardGeofence.initialize(), 500);

        // Initialize who is out today
        DashboardWhoIsOut.load();
    }

    // ============================================
    // WHO IS OUT TODAY
    // ============================================
    const DashboardWhoIsOut = {
        load() {
            const url = (window._dashRoutes && window._dashRoutes.whoIsOutToday) ?
                window._dashRoutes.whoIsOutToday :
                '/admin/dashboard/who-is-out';

            const loading = document.getElementById('whoIsOutLoading');
            const countBadge = document.getElementById('whoIsOutCount');

            if (loading) loading.classList.remove('d-none');

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(function (res) {
                    return res.json();
                })
                .then(function (json) {
                    if (loading) loading.classList.add('d-none');
                    if (!json.success) return;
                    DashboardWhoIsOut.render(json.data);
                    if (countBadge) countBadge.textContent = json.count || 0;
                })
                .catch(function (err) {
                    console.error('Who is out load failed:', err);
                    if (loading) loading.innerHTML = '<span class="text-danger small">Failed to load.</span>';
                });
        },

        render(items) {
            const container = document.getElementById('whoIsOutContainer');
            const template = document.getElementById('whoIsOutTemplate');
            if (!container || !template) return;

            // Clear existing items
            container.querySelectorAll('.avatar-gallery-item').forEach(el => el.remove());

            if (!items || items.length === 0) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="empty-state w-100" style="grid-column: 1 / -1;">
                        <div class="empty-state-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div class="empty-state-title">All Present!</div>
                        <p class="empty-state-text">No employees are on leave today.</p>
                    </div>
                `);
                return;
            }

            items.forEach(function (item) {
                const clone = template.content.cloneNode(true);
                const wrapper = clone.querySelector('.avatar-gallery-item');
                
                wrapper.setAttribute('title', item.name + ' — ' + item.leave_type);
                wrapper.querySelector('.avatar-gallery-name').textContent = item.short_name;
                wrapper.querySelector('.avatar-gallery-role').textContent = item.leave_type_short || item.leave_type;
                
                const avatarContainer = wrapper.querySelector('.avatar-gallery-avatar');
                if (item.avatar_url) {
                    const img = document.createElement('img');
                    img.src = item.avatar_url;
                    img.alt = item.initials;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '50%';
                    
                    avatarContainer.innerHTML = '';
                    avatarContainer.appendChild(img);
                    
                    if (item.status_dot) {
                        const dot = document.createElement('span');
                        dot.className = 'avatar-status-dot ' + item.status_dot;
                        avatarContainer.appendChild(dot);
                    }
                } else {
                    wrapper.querySelector('.initials').textContent = item.initials;
                    if (item.status_dot) {
                        const dot = wrapper.querySelector('.avatar-status-dot');
                        if (dot) dot.className = 'avatar-status-dot ' + item.status_dot;
                    }
                }

                container.appendChild(clone);
            });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }

    //helper function for page reload
    function forceCloseSlideOver() {
        const backdrop = document.getElementById('slideOverBackdrop');
        const panel = document.getElementById('slideOverPanel');

        if (backdrop) backdrop.classList.remove('show');
        if (panel) panel.classList.remove('show');

        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        forceCloseSlideOver();
    });

    document.addEventListener('DOMContentLoaded', function () {
        forceCloseSlideOver();
    });
})();
