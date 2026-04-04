<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="userCanvas" aria-labelledby="userCanvasLabel" style="width:420px;">
    <div class="offcanvas-header border-bottom" style="border-color:#ffffff42 !important">
        <h5 class="offcanvas-title" id="userCanvasLabel">Add New User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">

        <div class="text-center mb-4">
            <img id="userCanvasAvatar" src="https://ui-avatars.com/api/?name=User&background=e6c673&color=000&size=80" alt="Avatar" class="rounded-circle border border-2" style="width: 80px; height: 80px; object-fit: cover; border-color: var(--primary-color) !important;">
            <div class="small mt-2 text-white-50">Profile image is managed via Employee record</div>
        </div>

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
                        @php
                            $prefillEmail = trim((string) ($emp->email ?: $emp->contact?->email ?? ''));
                            $sbuName      = $emp->sbu?->name ?? '-';
                            $photo        = $emp->mediaFiles->where('file_type', 'photo')->first();
                            $avatarUrl    = $photo && $photo->file_path ? asset('storage/' . $photo->file_path) : '';
                        @endphp
                        <option value="{{ $emp->id }}"
                            data-name="{{ $emp->full_name }}"
                            data-email="{{ e($prefillEmail) }}"
                            data-sbu="{{ e($sbuName) }}"
                            data-avatar="{{ $avatarUrl }}"
                            data-role="{{ $emp->role?->name ?? '' }}">
                            {{ $emp->employee_code }} — {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
                <small class="opacity-50">Only employees without an account are listed. Auto-fills name, email & SBU.</small>
                <div class="field-error text-danger small mt-1 d-none" id="err_employee_id"></div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">SBU</label>
                <input type="text" class="form-control form-control-sm" id="userAssignedSbu"
                    placeholder="SBU will auto-fill from employee registration"
                    style="background:transparent;border-color:#ffffff42;color:#fff;" readonly>
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
                    placeholder="Work email (filled from employee if available)" autocomplete="off"
                    style="background:transparent;border-color:#ffffff42;color:#fff;">
                <small class="opacity-50 d-none mt-1" id="emailManualHint">No email on employee record — enter one to create the account.</small>
                <div class="field-error text-danger small mt-1 d-none" id="err_email"></div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Assigned Role</label>
                <input type="text" class="form-control form-control-sm" id="userAssignedRole"
                    placeholder="Role will auto-fill from employee registration"
                    style="background:transparent;border-color:#ffffff42;color:#fff;" readonly>
            </div>

            <p class="small opacity-75 mb-0" id="createUserPasswordNote">
                A temporary password will be emailed to this address. The user must sign in and set a new password.
            </p>

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
