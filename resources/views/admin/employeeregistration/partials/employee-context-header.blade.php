@php
    $ctxPhotoUrl = '';
    if (isset($employee)) {
        $photo = $employee->mediaFiles()->where('file_type', 'photo')->first();
        if ($photo) {
            $ctxPhotoUrl = asset('storage/' . $photo->file_path);
        }
    }

    $ctxName = trim($employee->full_name ?? '');
    if ($ctxName === '' && isset($employee)) {
        $ctxName = trim(collect([$employee->first_name ?? '', $employee->middle_name ?? '', $employee->last_name ?? ''])->filter()->implode(' '));
    }

    $ctxParts = collect(preg_split('/\s+/', $ctxName, -1, PREG_SPLIT_NO_EMPTY));
    $ctxInitials = $ctxParts->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    if ($ctxInitials === '') {
        $ctxInitials = '?';
    }

    $ctxCode = $employee->employee_code ?? '';
@endphp

<div id="employeeContextHeader" class="employee-context-header d-none">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-3 py-3">
        <div class="d-flex align-items-center gap-3 min-w-0">
            <div class="employee-context-avatar flex-shrink-0 position-relative">
                <img id="contextEmployeeAvatar" src="{{ $ctxPhotoUrl }}"
                    alt=""
                    class="employee-context-avatar-img {{ empty($ctxPhotoUrl) ? 'd-none' : '' }}">
                <div id="contextEmployeeInitialsWrap"
                    class="employee-context-avatar-initials {{ !empty($ctxPhotoUrl) ? 'd-none' : '' }}">
                    <span id="contextEmployeeInitials">{{ $ctxInitials }}</span>
                </div>
            </div>

            <div class="min-w-0">
                <div id="contextEmployeeName" class="employee-context-name text-truncate">
                    {{ $ctxName !== '' ? $ctxName : 'New Employee' }}
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 mt-1 employee-context-meta">
                    <span class="d-inline-flex align-items-center gap-1">
                        <i class="bi bi-person-vcard"></i>
                        <span id="contextEmployeeCode">{{ $ctxCode !== '' ? $ctxCode : 'Pending' }}</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column align-items-end gap-2 flex-shrink-0 ms-auto">
            <span class="employee-context-draft-badge">
                <i class="bi bi-check-circle-fill"></i>
                Draft saved
            </span>
            <div class="d-flex align-items-center gap-2">
                <div id="contextStepDots" class="employee-context-dots" aria-hidden="true"></div>
                <span id="contextStepLabel" class="employee-context-step-label">Step 2 of 5</span>
            </div>
        </div>
    </div>
</div>
