<!-- Employee Module Header -->
<div class="row align-items-center p-4">
    <div class="d-flex align-items-center gap-2 col-md-6">
        <h5 class="mb-0">Employee Directory</h5>
        <div class="d-flex mb-2">
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary active" id="btnTableView" onclick="switchView('table')"
                    title="Table View">
                    <i class="bi bi-table"></i>
                </button>
                <button class="btn btn-outline-secondary" id="btnGridView" onclick="switchView('grid')"
                    title="Grid View">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-outline-secondary me-2" id="exportBtn">
            <i class="bi bi-download me-1"></i>Export
        </button>
        <a href="{{ route('admin.register.index') }}" class="btn btn-primary bg-main border-0 me-2">
            <i class="bi bi-person-plus me-1"></i>Add New Employee
        </a>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false" id="filterDropdownBtn">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
                <!-- Employee Type Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Employee Type</label>
                        <select class="form-select form-select-sm" id="filterEmployeeType">
                            <option value="">All Types</option>
                            <option value="Internal">Internal</option>
                            <option value="Third-party">Third-party</option>
                        </select>
                    </div>
                </li>
                <!-- Department Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Department</label>
                        <select class="form-select form-select-sm" id="filterDepartment">
                            <option value="">All Departments</option>
                            @isset($departments)
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                </li>
                <!-- Vendor Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Vendor</label>
                        <select class="form-select form-select-sm" id="filterVendor">
                            <option value="">All Vendors</option>
                            <option value="TechStaff Solutions">TechStaff Solutions</option>
                            <option value="Global Workforce Inc">Global Workforce Inc</option>
                            <option value="StaffPro Services">StaffPro Services</option>
                            <option value="Manpower Group">Manpower Group</option>
                            <option value="Adecco">Adecco</option>
                        </select>
                    </div>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="clearFiltersBtn">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </button>
                        <button type="button" class="btn btn-sm btn-primary bg-main border-0 flex-fill"
                            id="applyFiltersBtn">
                            <i class="bi bi-check-lg me-1"></i>Apply
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
