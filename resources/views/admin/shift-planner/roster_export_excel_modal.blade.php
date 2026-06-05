<div class="modal fade" id="rosterExportExcelModal" tabindex="-1" aria-labelledby="rosterExportExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered roster-export-excel-dialog">
        <div class="modal-content roster-export-excel-content border-0 shadow-lg">
            <div class="modal-header roster-export-excel-header border-0">
                <h5 class="modal-title d-flex align-items-center gap-2 mb-0" id="rosterExportExcelModalLabel">
                    <span class="roster-export-excel-header-icon" aria-hidden="true">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </span>
                    Export shifts to Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body roster-export-excel-body">
                <section class="roster-export-excel-section" aria-labelledby="rosterExportExcelMonthLabel">
                    <p class="roster-export-excel-section-label" id="rosterExportExcelMonthLabel">Select month</p>
                    <div class="roster-export-excel-month-grid" id="rosterExportExcelMonthGrid" role="group" aria-label="Month">
                        @foreach (['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] as $index => $label)
                            <button type="button"
                                class="roster-export-excel-month-btn"
                                data-month="{{ $index }}"
                                aria-pressed="false">{{ $label }}</button>
                        @endforeach
                    </div>
                </section>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label for="rosterExportExcelYear" class="roster-export-excel-field-label">Year</label>
                        <select class="form-select roster-export-excel-select" id="rosterExportExcelYear" name="year"></select>
                    </div>
                    <div class="col-sm-6">
                        <label for="rosterExportExcelEmployeeGroup" class="roster-export-excel-field-label">Employee group</label>
                        <select class="form-select roster-export-excel-select" id="rosterExportExcelEmployeeGroup" name="employee_group">
                            <option value="internal">Internal employees</option>
                            <option value="third_party">Third-party employees</option>
                        </select>
                    </div>
                </div>

                <section class="roster-export-excel-section mb-0" aria-labelledby="rosterExportExcelIncludeLabel">
                    <p class="roster-export-excel-section-label" id="rosterExportExcelIncludeLabel">Include in export</p>
                    <div class="roster-export-excel-include-list">
                        <div class="form-check roster-export-excel-include-item mb-0">
                            <input class="form-check-input" type="checkbox" id="rosterExportExcelIncludeDeleted" value="1">
                            <label class="form-check-label" for="rosterExportExcelIncludeDeleted">Show deleted shifts</label>
                        </div>
                    </div>
                </section>
            </div>
            <div class="modal-footer roster-export-excel-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary bg-main border-0 rounded-2" id="rosterExportExcelSubmitBtn">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Excel
                </button>
            </div>
        </div>
    </div>
</div>
