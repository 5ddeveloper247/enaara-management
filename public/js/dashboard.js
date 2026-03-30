(function () {
    'use strict';

    // ============================================
    // DATA (using common ProjectData)
    // ============================================
    const DashboardData = ProjectData.dashboard;

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
         * Initialize Attendance Overview Chart
         */
        initAttendanceChart() {
            const ctx = document.getElementById('attendanceChart');
            if (!ctx) return;

            const colors = DashboardUtils.getThemeColors();

            this.attendanceChart = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: DashboardData.attendance[7].labels,
                    datasets: [{
                        label: 'Present',
                        data: DashboardData.attendance[7].present,
                        backgroundColor: colors.primaryColor,
                        borderWidth: 0,
                        borderRadius: 1000
                    }, {
                        label: 'Absent',
                        data: DashboardData.attendance[7].absent,
                        backgroundColor: '#dc3545',
                        borderWidth: 0,
                        borderRadius: 1000
                    }, {
                        label: 'On Leave',
                        data: DashboardData.attendance[7].onLeave,
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
                                color: '#000000'
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
        },

        /**
         * Update attendance chart data based on selected period
         */
        updateAttendanceData(period) {
            if (!this.attendanceChart) return;

            const data = DashboardData.attendance[period];
            if (!data) {
                console.error('Invalid period:', period);
                return;
            }

            this.attendanceChart.data.labels = data.labels;
            this.attendanceChart.data.datasets[0].data = data.present;
            this.attendanceChart.data.datasets[1].data = data.absent;
            this.attendanceChart.data.datasets[2].data = data.onLeave;
            this.attendanceChart.update();
        },

        /**
         * Initialize period buttons
         */
        initPeriodButtons() {
            const periodButtons = document.querySelectorAll('.period-btn');
            periodButtons.forEach(button => {
                button.addEventListener('click', function () {
                    periodButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const period = parseInt(this.getAttribute('data-period'));
                    DashboardCharts.updateAttendanceData(period);
                });
            });
        },

        /**
         * Initialize Department Distribution Chart
         */
        initDepartmentChart() {
            const ctx = document.getElementById('departmentChart');
            if (!ctx) return;

            const colors = DashboardUtils.getThemeColors();
            const baseColor = colors.primaryColor || '#012445';

            const colorVariations = [
                baseColor,
                DashboardUtils.lightenColor(baseColor, 0.15),
                DashboardUtils.lightenColor(baseColor, 0.30),
                DashboardUtils.lightenColor(baseColor, 0.50)
            ];

            this.departmentChart = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: DashboardData.department.labels,
                    datasets: DashboardData.department.datasets.map((dataset, index) => ({
                        label: dataset.label,
                        data: dataset.data,
                        borderColor: colorVariations[index],
                        backgroundColor: DashboardUtils.hexToRgba(colorVariations[index], 0.3),
                        borderWidth: 3,
                        fill: true,
                        tension: 0.5,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }))
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

            this.createCustomLegend();
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
            const departmentData = DashboardData.workforce.departmentData;
            const sum = departmentData.reduce((total, deptData) => {
                return total + deptData[deptData.length - 1];
            }, 0);
            const average = Math.round(sum / departmentData.length);
            const percentage = average;
            const totalEmployees = DashboardData.workforce.totalEmployees;
            const activeEmployees = Math.round((percentage / 100) * totalEmployees);

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

        /**
         * Initialize bulk approve functionality
         */
        initBulkApprove() {
            const bulkApproveAll = document.getElementById('bulkApproveAll');
            if (!bulkApproveAll) return;

            bulkApproveAll.addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.approval-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                DashboardApprovals.updateBulkApproveButton();
            });

            document.querySelectorAll('.approval-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    DashboardApprovals.updateBulkApproveButton();
                    DashboardApprovals.updateSelectAllCheckbox();
                });
            });

            const bulkApproveBtn = document.getElementById('bulkApproveBtn');
            if (bulkApproveBtn) {
                bulkApproveBtn.addEventListener('click', function () {
                    const checkedBoxes = document.querySelectorAll('.approval-checkbox:checked');
                    const ids = Array.from(checkedBoxes).map(cb => cb.value);

                    if (ids.length > 10) {
                        DashboardUtils.showAlert('You can only approve up to 10 requests at once. Please select fewer requests.');
                        return;
                    }

                    if (DashboardUtils.confirmAction(`Are you sure you want to approve ${ids.length} leave request(s)?`)) {
                        DashboardApprovals.bulkApprove(ids);
                    }
                });
            }
        },

        /**
         * Update bulk approve button state
         */
        updateBulkApproveButton() {
            const checkedBoxes = document.querySelectorAll('.approval-checkbox:checked');
            const bulkBtn = document.getElementById('bulkApproveBtn');
            const countSpan = document.getElementById('selectedCount');

            if (checkedBoxes.length > 0) {
                if (bulkBtn) {
                    bulkBtn.classList.remove('d-none');
                    bulkBtn.disabled = false;
                }
                if (countSpan) countSpan.textContent = checkedBoxes.length;
            } else {
                if (bulkBtn) {
                    bulkBtn.classList.add('d-none');
                    bulkBtn.disabled = true;
                }
            }
        },

        /**
         * Update select all checkbox state
         */
        updateSelectAllCheckbox() {
            const checkboxes = document.querySelectorAll('.approval-checkbox');
            const selectAll = document.getElementById('bulkApproveAll');
            const checkedCount = document.querySelectorAll('.approval-checkbox:checked').length;

            if (!selectAll) return;

            if (checkedCount === 0) {
                selectAll.indeterminate = false;
                selectAll.checked = false;
            } else if (checkedCount === checkboxes.length) {
                selectAll.indeterminate = false;
                selectAll.checked = true;
            } else {
                selectAll.indeterminate = true;
            }
        },

        /**
         * Bulk approve implementation
         */
        bulkApprove(ids) {
            console.log('Bulk approving:', ids);

            ids.forEach(id => {
                const item = document.querySelector(`.approval-item[data-approval-id="${id}"]`);
                if (item) {
                    item.style.transition = 'opacity 0.3s ease';
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.remove();
                        DashboardApprovals.updatePendingCount();
                    }, 300);
                }
            });

            const bulkApproveAll = document.getElementById('bulkApproveAll');
            if (bulkApproveAll) bulkApproveAll.checked = false;
            this.updateBulkApproveButton();

            DashboardUtils.showAlert(`${ids.length} leave request(s) approved successfully!`);
        },

        /**
         * Initialize individual approve/reject buttons
         */
        initApprovalButtons() {
            document.querySelectorAll('.approve-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    if (DashboardUtils.confirmAction('Are you sure you want to approve this leave request?')) {
                        DashboardApprovals.approveRequest(id);
                    }
                });
            });

            document.querySelectorAll('.reject-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    if (DashboardUtils.confirmAction('Are you sure you want to reject this leave request?')) {
                        DashboardApprovals.rejectRequest(id);
                    }
                });
            });
        },

        /**
         * Approve single request
         */
        approveRequest(id) {
            console.log('Approving request:', id);

            const item = document.querySelector(`.approval-item[data-approval-id="${id}"]`);
            if (item) {
                item.style.transition = 'opacity 0.3s ease';
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    this.updatePendingCount();
                }, 300);
            }

            DashboardUtils.showAlert('Leave request approved successfully!');
        },

        /**
         * Reject single request
         */
        rejectRequest(id) {
            console.log('Rejecting request:', id);

            const item = document.querySelector(`.approval-item[data-approval-id="${id}"]`);
            if (item) {
                item.style.transition = 'opacity 0.3s ease';
                item.style.opacity = '0';
                setTimeout(() => {
                    item.remove();
                    this.updatePendingCount();
                }, 300);
            }

            DashboardUtils.showAlert('Leave request rejected.');
        },

        /**
         * Update pending count badge and show/hide empty state
         */
        updatePendingCount() {
            const items = document.querySelectorAll('.approval-item').length;
            const pendingCard = document.querySelector('.card-header');
            const pendingBadge = pendingCard ? pendingCard.querySelector('.badge') : null;
            const emptyState = document.getElementById('pendingApprovalsEmpty');
            const approvalItems = document.querySelectorAll('.approval-item');
            const bulkHeader = document.querySelector('.bulk-approve-header');

            if (pendingBadge) {
                pendingBadge.textContent = items;
            }

            if (items === 0) {
                if (emptyState) emptyState.classList.remove('d-none');
                if (approvalItems.length > 0) {
                    approvalItems.forEach(item => item.style.display = 'none');
                }
                if (bulkHeader) bulkHeader.style.display = 'none';
            } else {
                if (emptyState) emptyState.classList.add('d-none');
                if (approvalItems.length > 0) {
                    approvalItems.forEach(item => item.style.display = '');
                }
                if (bulkHeader) bulkHeader.style.display = '';
            }
        },

        /**
         * View leave reason - opens slide-over panel
         */
        viewLeaveReason(id, name, initials, leaveType, requestDate, startDate, endDate, reason) {
            this.currentLeaveId = id;

            const avatarEl = document.getElementById('slideEmployeeAvatar');
            const nameEl = document.getElementById('slideEmployeeName');
            const typeEl = document.getElementById('slideLeaveType');
            const requestDateEl = document.getElementById('slideRequestDate');
            const startDateEl = document.getElementById('slideStartDate');
            const endDateEl = document.getElementById('slideEndDate');
            const reasonEl = document.getElementById('slideReason');

            if (avatarEl) avatarEl.textContent = initials;
            if (nameEl) nameEl.textContent = name;
            if (typeEl) typeEl.textContent = leaveType;
            if (requestDateEl) requestDateEl.textContent = requestDate;
            if (startDateEl) startDateEl.textContent = startDate;
            if (endDateEl) endDateEl.textContent = endDate;
            if (reasonEl) reasonEl.textContent = reason;

            const backdrop = document.getElementById('slideOverBackdrop');
            const panel = document.getElementById('slideOverPanel');
            if (backdrop) backdrop.classList.add('show');
            if (panel) panel.classList.add('show');
            document.body.style.overflow = 'hidden';
        },

        /**
         * Close slide-over panel
         */
        closeSlideOver() {
            const backdrop = document.getElementById('slideOverBackdrop');
            const panel = document.getElementById('slideOverPanel');
            if (backdrop) backdrop.classList.remove('show');
            if (panel) panel.classList.remove('show');
            document.body.style.overflow = '';
            this.currentLeaveId = null;
        },

        /**
         * Approve from slide-over
         */
        approveFromSlide() {
            if (!this.currentLeaveId) return;

            if (DashboardUtils.confirmAction('Are you sure you want to approve this leave request?')) {
                this.approveRequest(this.currentLeaveId);
                this.closeSlideOver();
            }
        },

        /**
         * Reject from slide-over
         */
        rejectFromSlide() {
            if (!this.currentLeaveId) return;

            if (DashboardUtils.confirmAction('Are you sure you want to reject this leave request?')) {
                this.rejectRequest(this.currentLeaveId);
                this.closeSlideOver();
            }
        },

        /**
         * Initialize slide-over keyboard shortcuts
         */
        initSlideOverKeyboard() {
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    DashboardApprovals.closeSlideOver();
                }
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
    window.viewLeaveReason = function (id, name, initials, leaveType, requestDate, startDate, endDate, reason) {
        DashboardApprovals.viewLeaveReason(id, name, initials, leaveType, requestDate, startDate, endDate, reason);
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
        DashboardApprovals.initBulkApprove();
        DashboardApprovals.initApprovalButtons();
        DashboardApprovals.initSlideOverKeyboard();
        DashboardApprovals.updatePendingCount();

        // Initialize exception management
        DashboardExceptions.initNotifyButtons();

        // Initialize geofence map
        setTimeout(() => DashboardGeofence.initialize(), 500);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }
})();
