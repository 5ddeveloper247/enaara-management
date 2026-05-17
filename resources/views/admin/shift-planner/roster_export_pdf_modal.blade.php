<div class="modal fade" id="rosterExportPdfModal" tabindex="-1" aria-labelledby="rosterExportPdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered roster-export-pdf-dialog">
        <div class="modal-content roster-export-pdf-content border-0 shadow-lg">
            <div class="modal-header roster-export-pdf-header border-0">
                <h5 class="modal-title d-flex align-items-center gap-2 mb-0" id="rosterExportPdfModalLabel">
                    <span class="roster-export-pdf-header-icon" aria-hidden="true">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </span>
                    Export shifts to PDF
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body roster-export-pdf-body">
                <section class="roster-export-pdf-section" aria-labelledby="rosterExportMonthLabel">
                    <p class="roster-export-pdf-section-label" id="rosterExportMonthLabel">Select month</p>
                    <div class="roster-export-month-grid" id="rosterExportMonthGrid" role="group" aria-label="Month">
                        @foreach (['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $index => $label)
                            <button type="button"
                                class="roster-export-month-btn"
                                data-month="{{ $index }}"
                                aria-pressed="false">{{ $label }}</button>
                        @endforeach
                    </div>
                </section>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label for="rosterExportYear" class="roster-export-pdf-field-label">Year</label>
                        <select class="form-select roster-export-pdf-select" id="rosterExportYear" name="year"></select>
                    </div>
                    <div class="col-sm-6">
                        <label for="rosterExportEmployeeGroup" class="roster-export-pdf-field-label">Employee group</label>
                        <select class="form-select roster-export-pdf-select" id="rosterExportEmployeeGroup" name="employee_group">
                            <option value="internal">Internal employees</option>
                            <option value="third_party">Third-party employees</option>
                        </select>
                    </div>
                </div>

                <section class="roster-export-pdf-section mb-0" aria-labelledby="rosterExportIncludeLabel">
                    <p class="roster-export-pdf-section-label" id="rosterExportIncludeLabel">Include in export</p>
                    <div class="roster-export-include-list">
                        <div class="form-check roster-export-include-item">
                            <input class="form-check-input" type="checkbox" id="rosterExportIncludeTimes" value="1" checked>
                            <label class="form-check-label" for="rosterExportIncludeTimes">Shift times (start &amp; end)</label>
                        </div>
                        <div class="form-check roster-export-include-item">
                            <input class="form-check-input" type="checkbox" id="rosterExportIncludeDept" value="1" checked>
                            <label class="form-check-label" for="rosterExportIncludeDept">Department / team grouping</label>
                        </div>
                        <div class="form-check roster-export-include-item mb-0">
                            <input class="form-check-input" type="checkbox" id="rosterExportIncludeDeleted" value="1">
                            <label class="form-check-label" for="rosterExportIncludeDeleted">Show deleted shifts</label>
                        </div>
                    </div>
                </section>
            </div>
            <div class="modal-footer roster-export-pdf-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary bg-main border-0 rounded-2" id="rosterExportPdfSubmitBtn">
                    <i class="bi bi-download me-1"></i>Export PDF
                </button>
            </div>
        </div>
    </div>
</div>
