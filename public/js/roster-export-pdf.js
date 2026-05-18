(function() {
    var selectedMonth = new Date().getMonth();
    var exportPeriodType = 'month';
    var exportLayout = 'calendar';

    function getModalEl() {
        return document.getElementById('rosterExportPdfModal');
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function pad2(n) {
        return n < 10 ? '0' + n : String(n);
    }

    function dateToISO(date) {
        return date.getFullYear() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate());
    }

    function lastDayOfMonth(year, monthIndex) {
        return new Date(year, monthIndex + 1, 0);
    }

    function populateYearOptions() {
        var yearSelect = document.getElementById('rosterExportYear');
        if (!yearSelect) {
            return;
        }

        var currentYear = new Date().getFullYear();
        var html = '';
        for (var y = currentYear - 2; y <= currentYear + 2; y++) {
            html += '<option value="' + y + '">' + y + '</option>';
        }
        yearSelect.innerHTML = html;
    }

    function setSelectedMonth(monthIndex) {
        selectedMonth = monthIndex;
        document.querySelectorAll('.roster-export-month-btn').forEach(function(btn) {
            var isActive = parseInt(btn.getAttribute('data-month'), 10) === monthIndex;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        syncDateRangeFromMonthYear();
    }

    function setExportLayout(layout) {
        var allowed = ['calendar', 'tabular', 'per_employee'];
        exportLayout = allowed.indexOf(layout) >= 0 ? layout : 'calendar';

        document.querySelectorAll('.roster-export-layout-card').forEach(function(card) {
            var isActive = card.getAttribute('data-export-layout') === exportLayout;
            card.classList.toggle('is-active', isActive);
            card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function setExportPeriodType(type) {
        exportPeriodType = type === 'date_range' ? 'date_range' : 'month';

        document.querySelectorAll('.roster-export-period-type-btn').forEach(function(btn) {
            var isActive = btn.getAttribute('data-period-type') === exportPeriodType;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        var monthPicker = document.getElementById('rosterExportMonthPicker');
        var yearCol = document.getElementById('rosterExportYearCol');
        var dateRangePanel = document.getElementById('rosterExportPeriodDateRange');

        if (monthPicker) {
            monthPicker.classList.toggle('d-none', exportPeriodType === 'date_range');
        }
        if (yearCol) {
            yearCol.classList.toggle('d-none', exportPeriodType === 'date_range');
        }
        if (dateRangePanel) {
            dateRangePanel.classList.toggle('d-none', exportPeriodType !== 'date_range');
        }

        clearDateRangeFieldErrors();

        if (exportPeriodType === 'date_range') {
            syncDateRangeFromMonthYear();
        }
    }

    function syncDateRangeFromMonthYear() {
        var yearSelect = document.getElementById('rosterExportYear');
        var startInput = document.getElementById('rosterExportStartDate');
        var endInput = document.getElementById('rosterExportEndDate');
        if (!startInput || !endInput) {
            return;
        }

        var year = parseInt(yearSelect?.value || String(new Date().getFullYear()), 10);
        if (!Number.isFinite(year)) {
            year = new Date().getFullYear();
        }

        var monthStart = new Date(year, selectedMonth, 1);
        var monthEnd = lastDayOfMonth(year, selectedMonth);
        startInput.value = dateToISO(monthStart);
        endInput.value = dateToISO(monthEnd);
    }

    function clearDateRangeFieldErrors() {
        ['rosterExportStartDate', 'rosterExportEndDate'].forEach(function(id) {
            var input = document.getElementById(id);
            var errorEl = document.getElementById(id + 'Error');
            if (input) {
                input.classList.remove('is-invalid');
            }
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.add('d-none');
            }
        });
    }

    function showDateRangeFieldError(fieldId, message) {
        var input = document.getElementById(fieldId);
        var errorEl = document.getElementById(fieldId + 'Error');
        if (input) {
            input.classList.add('is-invalid');
        }
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('d-none');
        }
    }

    function validateDateRangeFields() {
        clearDateRangeFieldErrors();

        var startInput = document.getElementById('rosterExportStartDate');
        var endInput = document.getElementById('rosterExportEndDate');
        var startVal = startInput?.value || '';
        var endVal = endInput?.value || '';
        var valid = true;

        if (!startVal) {
            showDateRangeFieldError('rosterExportStartDate', 'Start date is required.');
            valid = false;
        }
        if (!endVal) {
            showDateRangeFieldError('rosterExportEndDate', 'End date is required.');
            valid = false;
        }
        if (!valid) {
            return false;
        }

        var startDate = new Date(startVal + 'T00:00:00');
        var endDate = new Date(endVal + 'T00:00:00');
        if (endDate < startDate) {
            showDateRangeFieldError('rosterExportEndDate', 'End date must be on or after the start date.');
            valid = false;
        }

        var diffDays = Math.floor((endDate - startDate) / 86400000) + 1;
        if (diffDays > 366) {
            showDateRangeFieldError('rosterExportEndDate', 'Date range may not exceed 366 days.');
            valid = false;
        }

        return valid;
    }

    function syncFromRosterView() {
        var ctx = typeof window.getRosterExportContext === 'function'
            ? window.getRosterExportContext()
            : null;

        var year = ctx ? ctx.year : new Date().getFullYear();
        var month = ctx ? ctx.month : new Date().getMonth();
        var filter = ctx ? ctx.personnelFilter : 'internal';
        var showDeleted = ctx ? !!ctx.showDeleted : false;

        var yearSelect = document.getElementById('rosterExportYear');
        if (yearSelect) {
            yearSelect.value = String(year);
        }

        setSelectedMonth(month);
        setExportPeriodType('month');
        setExportLayout('calendar');

        var groupSelect = document.getElementById('rosterExportEmployeeGroup');
        if (groupSelect) {
            groupSelect.value = filter === 'third_party' ? 'third_party' : 'internal';
        }

        var deletedCb = document.getElementById('rosterExportIncludeDeleted');
        if (deletedCb) {
            deletedCb.checked = showDeleted;
        }
    }

    function collectExportOptions() {
        var base = {
            export_period_type: exportPeriodType,
            export_layout: exportLayout,
            employee_group: document.getElementById('rosterExportEmployeeGroup')?.value || 'internal',
            include_shift_times: !!document.getElementById('rosterExportIncludeTimes')?.checked,
            include_department_grouping: !!document.getElementById('rosterExportIncludeDept')?.checked,
            include_deleted: !!document.getElementById('rosterExportIncludeDeleted')?.checked
        };

        if (exportPeriodType === 'date_range') {
            base.start_date = document.getElementById('rosterExportStartDate')?.value || '';
            base.end_date = document.getElementById('rosterExportEndDate')?.value || '';
            return base;
        }

        base.year = parseInt(document.getElementById('rosterExportYear')?.value || '0', 10);
        base.month = selectedMonth + 1;
        return base;
    }

    function openExportModal() {
        var modalEl = getModalEl();
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }
        syncFromRosterView();
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function downloadBlob(blob, filename) {
        var url = window.URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    }

    function filenameFromResponse(response, fallback) {
        var disposition = response.headers.get('Content-Disposition') || '';
        var match = disposition.match(/filename="?([^";]+)"?/i);
        return match && match[1] ? match[1] : fallback;
    }

    function hideExportModal() {
        var modalEl = getModalEl();
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }
        var instance = bootstrap.Modal.getInstance(modalEl);
        if (instance) {
            instance.hide();
        }
    }

    function parseErrorMessage(response, bodyText) {
        if (!bodyText) {
            return 'Could not export PDF.';
        }
        try {
            var body = JSON.parse(bodyText);
            if (body.message) {
                return body.message;
            }
            if (body.errors) {
                var messages = [];
                Object.keys(body.errors).forEach(function(key) {
                    var fieldErrors = body.errors[key];
                    if (Array.isArray(fieldErrors)) {
                        messages = messages.concat(fieldErrors);
                    }
                });
                if (messages.length) {
                    return messages.join('\n');
                }
            }
        } catch (e) {
            return 'Could not export PDF.';
        }
        return 'Could not export PDF.';
    }

    function showExportError(message) {
        hideExportModal();
        if (typeof showError === 'function') {
            showError(message || 'Could not export PDF.');
        }
    }

    function handleExportSubmit() {
        var options = collectExportOptions();
        var exportUrl = window.rosterExportPdfUrl || '';

        if (!exportUrl) {
            if (typeof showError === 'function') {
                showError('Export URL is not configured.');
            }
            return;
        }

        if (options.export_period_type === 'date_range') {
            if (!validateDateRangeFields()) {
                return;
            }
        } else if (!options.year || options.month < 1 || options.month > 12) {
            if (typeof showError === 'function') {
                showError('Please select a valid month and year.');
            }
            return;
        }

        var submitBtn = document.getElementById('rosterExportPdfSubmitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';
        }

        fetch(exportUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/pdf, application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(options),
            credentials: 'same-origin'
        })
            .then(function(response) {
                var contentType = response.headers.get('Content-Type') || '';
                if (!response.ok) {
                    return response.text().then(function(bodyText) {
                        throw new Error(parseErrorMessage(response, bodyText));
                    });
                }
                if (contentType.indexOf('application/pdf') === -1) {
                    return response.text().then(function(bodyText) {
                        throw new Error(parseErrorMessage(response, bodyText) || 'Unexpected export response.');
                    });
                }
                return response.blob().then(function(blob) {
                    return {
                        blob: blob,
                        filename: filenameFromResponse(response, 'shift-roster-export.pdf')
                    };
                });
            })
            .then(function(result) {
                hideExportModal();
                downloadBlob(result.blob, result.filename);
                if (typeof showSuccess === 'function') {
                    showSuccess('PDF exported successfully.');
                }
            })
            .catch(function(err) {
                showExportError(err.message || 'Could not export PDF.');
            })
            .finally(function() {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-download me-1"></i>Export PDF';
                }
            });
    }

    function bindEvents() {
        var openBtn = document.getElementById('rosterExportPdfBtn');
        if (openBtn) {
            openBtn.addEventListener('click', openExportModal);
        }

        document.querySelectorAll('.roster-export-month-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                setSelectedMonth(parseInt(this.getAttribute('data-month'), 10));
            });
        });

        document.querySelectorAll('.roster-export-period-type-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                setExportPeriodType(this.getAttribute('data-period-type'));
            });
        });

        document.querySelectorAll('.roster-export-layout-card').forEach(function(card) {
            card.addEventListener('click', function() {
                setExportLayout(this.getAttribute('data-export-layout'));
            });
        });

        var yearSelect = document.getElementById('rosterExportYear');
        if (yearSelect) {
            yearSelect.addEventListener('change', function() {
                if (exportPeriodType === 'date_range') {
                    syncDateRangeFromMonthYear();
                }
            });
        }

        var submitBtn = document.getElementById('rosterExportPdfSubmitBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', handleExportSubmit);
        }
    }

    function init() {
        populateYearOptions();
        setSelectedMonth(new Date().getMonth());
        setExportPeriodType('month');
        setExportLayout('calendar');
        bindEvents();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.openRosterExportPdfModal = openExportModal;
    window.collectRosterExportPdfOptions = collectExportOptions;
    window.getRosterExportLayout = function() {
        return exportLayout;
    };
})();
