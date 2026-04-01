<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="userCanvas" aria-labelledby="userCanvasLabel" style="width:420px;">
    <div class="offcanvas-header border-bottom" style="border-color:#ffffff42 !important">
        <h5 class="offcanvas-title" id="userCanvasLabel">Add New User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">

        <div id="userFormAlert" class="alert d-none mb-3 small fw-semibold" role="alert"></div>

        <form id="userForm" novalidate>
            <input type="hidden" id="editUserId" name="user_id" value="">

            {{-- Link to Employee --}}
            <div class="mb-3">
                <label class="form-label small fw-semibold">
                    Link to Employee <span class="text-danger">*</span>
                </label>
                <select class="form-select form-select-sm bg-transparent text-white" id="userEmployeeSelect" name="employee_id"
                    style="border-color:#ffffff42; background-color:transparent !important;" required>
                    <option value="">— Select an employee —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}"
                            data-name="{{ $emp->full_name }}"
                            data-email="{{ $emp->email ?? '' }}"
                            data-role="{{ $emp->role?->name ?? '' }}">
                            {{ $emp->employee_code }} — {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
                <small class="opacity-50">Only employees without an account are listed. Auto-fills name & email.</small>
                <div class="field-error text-danger small mt-1 d-none" id="err_employee_id"></div>
            </div>

            <hr class="my-3" style="border-color:#ffffff30 !important">

            {{-- Account Info --}}
            <div class="mb-3">
                <label class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="userName" name="name"
                    placeholder="Full name" autocomplete="off"
                    style="background:transparent;border-color:#ffffff42;color:#fff;">
                <div class="field-error text-danger small mt-1 d-none" id="err_name"></div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control form-control-sm" id="userEmail" name="email"
                    placeholder="user@example.com" autocomplete="off"
                    style="background:transparent;border-color:#ffffff42;color:#fff;">
                <div class="field-error text-danger small mt-1 d-none" id="err_email"></div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Assigned Role</label>
                <input type="text" class="form-control form-control-sm" id="userAssignedRole"
                    placeholder="Role will auto-fill from employee registration"
                    style="background:transparent;border-color:#ffffff42;color:#fff;" readonly>
            </div>

            <hr class="my-3" style="border-color:#ffffff30 !important">

            {{-- Password --}}
            <div id="passwordSection">
                <p class="small fw-semibold mb-2">Password <span id="passwordRequired" class="text-danger">*</span></p>
                <div class="mb-3">
                    <label class="form-label small opacity-75">New Password</label>
                    <input type="password" class="form-control form-control-sm" id="userPassword" name="password"
                        placeholder="Min. 8 chars, upper+lower+number"
                        style="background:transparent;border-color:#ffffff42;color:#fff;">
                    <div class="field-error text-danger small mt-1 d-none" id="err_password"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small opacity-75">Confirm Password</label>
                    <input type="password" class="form-control form-control-sm" id="userPasswordConfirm" name="password_confirmation"
                        placeholder="Re-enter password"
                        style="background:transparent;border-color:#ffffff42;color:#fff;">
                    <div class="field-error text-danger small mt-1 d-none" id="err_password_confirmation"></div>
                </div>
                <small class="opacity-50 d-block mb-2" id="passwordHint">Must be at least 8 characters with uppercase, lowercase and numbers.</small>
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top" style="border-color:#ffffff30 !important">
                <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-sm btn-light text-main fw-semibold" id="userSubmitBtn">
                    <i class="bi bi-person-check me-1"></i>Create User
                </button>
            </div>
        </form>
    </div>
</div>
