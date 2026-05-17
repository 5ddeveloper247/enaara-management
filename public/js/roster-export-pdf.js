(function() {
    var selectedMonth = new Date().getMonth();

    function getModalEl() {
        return document.getElementById('rosterExportPdfModal');
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
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
        return {
            year: parseInt(document.getElementById('rosterExportYear')?.value || '0', 10),
            month: selectedMonth + 1,
            employee_group: document.getElementById('rosterExportEmployeeGroup')?.value || 'internal',
            include_shift_times: !!document.getElementById('rosterExportIncludeTimes')?.checked,
            include_department_grouping: !!document.getElementById('rosterExportIncludeDept')?.checked,
            include_deleted: !!document.getElementById('rosterExportIncludeDeleted')?.checked
        };
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

        if (!options.year || options.month < 1 || options.month > 12) {
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

        var submitBtn = document.getElementById('rosterExportPdfSubmitBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', handleExportSubmit);
        }
    }

    function init() {
        populateYearOptions();
        setSelectedMonth(new Date().getMonth());
        bindEvents();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.openRosterExportPdfModal = openExportModal;
    window.collectRosterExportPdfOptions = collectExportOptions;
})();
