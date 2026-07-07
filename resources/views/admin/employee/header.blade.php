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
            <i class="bi bi-download me-1"></i>Export Record
        </button>
        @if(validatePermissions('admin/register'))
        <a href="{{ route('admin.register.index') }}"
            class="btn btn-primary bg-main border-0 me-2"
            id="addEmployeeActionBtn"
            data-internal-href="{{ route('admin.register.index') }}"
            data-outsourced-target="#outsourcedEmployeeCanvas">
            <i class="bi bi-person-plus me-1"></i>Add Employee
        </a>
        @endif
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"
                aria-expanded="false" id="filterDropdownBtn">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-3" style="min-width: 320px;">
                <!-- Employee Type Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Employee Category</label>
                        <select class="form-select form-select-sm" id="filterEmployeeType">
                            <option value="">All Categories</option>
                            <option value="Internal">Internal (Direct)</option>
                            <option value="Third-party">Third-party (Outsourced)</option>
                        </select>
                    </div>
                </li>
                <!-- Organization Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Organizations</label>
                        <select class="form-select form-select-sm" id="filterOrganization">
                            <option value="">All Organizations</option>
                            @isset($organizations)
                            @foreach ($organizations as $org)
                            <option value="{{ $org->name }}">{{ $org->name }}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>
                </li>
                <!-- SBU Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">SBU'S</label>
                        <select class="form-select form-select-sm" id="filterSbu">
                            <option value="">All SBU's</option>
                            @isset($sbus)
                            @foreach ($sbus as $sbu)
                            <option value="{{ $sbu->name }}">{{ $sbu->name }}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>
                </li>
                <!-- Department Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Department / Service Type</label>
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
                <!-- Role Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Role</label>
                        <select class="form-select form-select-sm" id="filterRole">
                            <option value="">All Roles</option>
                            @isset($roles)
                            @foreach ($roles->unique('name') as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                            @endisset
                        </select>
                    </div>
                </li>
                <!-- Gender Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Gender</label>
                        <select class="form-select form-select-sm" id="filterGender">
                            <option value="">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </li>
                <!-- Status Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select class="form-select form-select-sm" id="filterEmployeeStatus">
                            <option value="">All Statuses</option>
                            <option value="Active">Active</option>
                            <option value="Suspend">Suspend</option>
                            <option value="Terminated">Terminated</option>
                        </select>
                    </div>
                </li>
                <!-- Resource Type Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Resource Type</label>
                        <select class="form-select form-select-sm" id="filterResourceType">
                            <option value="">All Resource Types</option>
                            <option value="employee">Employee</option>
                            <option value="consultant">Consultant / Retainer</option>
                            <option value="intern">Intern</option>
                        </select>
                    </div>
                </li>
                <!-- Employment Type Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Employment Type</label>
                        <select class="form-select form-select-sm" id="filterEmploymentType">
                            <option value="">All Employment Types</option>
                            <option value="permanent">Permanent</option>
                            <option value="contractual">Contractual</option>
                            <option value="dailywages">Dailywages</option>
                        </select>
                    </div>
                </li>

                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Name</label>
                        <input type="text" class="form-control form-control-sm" id="filterName"
                            placeholder="Enter employee name">
                    </div>
                </li>

                <!-- CNIC Filter -->
                <li>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">CNIC</label>
                        <input type="text" class="form-control form-control-sm" id="filterCnic"
                            placeholder="Enter CNIC">
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
