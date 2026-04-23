<div class="offcanvas offcanvas-end bg-main text-white" tabindex="-1" id="editSbuCanvas"
    aria-labelledby="editSbuCanvasLabel" style="width: 600px;">

    <div class="offcanvas-header border-bottom" style="border-color: #ffffff42 !important">
        <h5 class="offcanvas-title" id="editSbuCanvasLabel">
            <i class="bi bi-pencil-square me-2"></i>Edit SBU
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
            aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <form id="editSbuForm" method="POST" action="javascript:void(0);" novalidate>
            @csrf
            <input type="hidden" id="edit_id" name="id">

            <div class="mb-3">
                <label for="edit_organization_id" class="form-label fw-semibold small text-white">
                    Organization <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_organization_id" name="organization_id" required>
                    <option value="" hidden selected>Select Organization</option>
                    @foreach ($organizations as $org)
                        <option value="{{ $org->id }}" data-working-days="{{ implode(',', $org->working_days ?? []) }}" data-working-start-time="{{ $org->working_start_time ? substr((string) $org->working_start_time, 0, 5) : '' }}" data-working-end-time="{{ $org->working_end_time ? substr((string) $org->working_end_time, 0, 5) : '' }}" data-opening-grace-period="{{ $org->opening_grace_period ?? '' }}" data-closing-grace-period="{{ $org->closing_grace_period ?? '' }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="edit_name" class="form-label fw-semibold small text-white">
                    SBU Name <span class="text-danger">*</span> <span class="text-white-50 fw-normal">(max 50)</span>
                </label>
                <input type="text" class="form-control" id="edit_name" name="name" placeholder="Enter SBU name" maxlength="50" required>
                <small class="d-block mt-1 text-white-50" id="editSbuNameMeta"><span id="editSbuNameLen">0</span> / 50</small>
            </div>

            <div class="mb-3">
                <label for="edit_city" class="form-label fw-semibold small text-white">
                    City <span class="text-white-50 fw-normal">(max 50)</span>
                </label>
                <input type="text" class="form-control" id="edit_city" name="city" placeholder="Enter city" maxlength="50">
                <small class="d-block mt-1 text-white-50" id="editSbuCityMeta"><span id="editSbuCityLen">0</span> / 50</small>
            </div>

            <div class="mb-3">
                <label for="edit_address" class="form-label fw-semibold small text-white">
                    Address <span class="text-white-50 fw-normal">(max 255)</span>
                </label>
                <textarea class="form-control" id="edit_address" name="address" rows="3" placeholder="Enter address" maxlength="255"></textarea>
                <small class="d-block mt-1 text-white-50" id="editSbuAddressMeta"><span id="editSbuAddressLen">0</span> / 255</small>
            </div>

            <div class="mb-3">
                <label for="edit_latitude" class="form-label fw-semibold small text-white">
                    Latitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="edit_latitude" name="latitude"
                    placeholder="e.g. 33.68442020">
            </div>

            <div class="mb-3">
                <label for="edit_longitude" class="form-label fw-semibold small text-white">
                    Longitude
                </label>
                <input type="number" step="0.00000001" class="form-control" id="edit_longitude" name="longitude"
                    placeholder="e.g. 73.04788480">
            </div>

            <div id="editSbuScheduleModeSection" class="mb-3 d-none">
                <label class="form-label fw-semibold small text-white">Selection Mode</label>
                <div class="btn-group w-100" role="group" aria-label="Selection Mode">
                    <input type="radio" class="btn-check" name="schedule_mode" id="editSbuScheduleModeStandard" value="standard" checked>
                    <label class="btn btn-outline-light" for="editSbuScheduleModeStandard">Standard</label>
                    <input type="radio" class="btn-check" name="schedule_mode" id="editSbuScheduleModeCustom" value="custom">
                    <label class="btn btn-outline-light" for="editSbuScheduleModeCustom">Custom</label>
                </div>
            </div>

            <div id="editSbuWorkingScheduleFields">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-white">Working Days</label>
                    <div class="d-flex flex-wrap gap-3">
                        @php($days = ['monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed', 'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun'])
                        @foreach($days as $dayValue => $dayLabel)
                            <div class="form-check">
                                <input class="form-check-input edit-sbu-working-day" type="checkbox" id="editSbuWorkingDay_{{ $dayValue }}" name="working_days[]" value="{{ $dayValue }}">
                                <label class="form-check-label small text-white" for="editSbuWorkingDay_{{ $dayValue }}">{{ $dayLabel }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="editSbuWorkingStartTime" class="form-label fw-semibold small text-white">Working Start Time</label>
                        <input type="time" class="form-control" id="editSbuWorkingStartTime" name="working_start_time">
                    </div>
                    <div class="col-6">
                        <label for="editSbuWorkingEndTime" class="form-label fw-semibold small text-white">Working End Time</label>
                        <input type="time" class="form-control" id="editSbuWorkingEndTime" name="working_end_time">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="editSbuOpeningGracePeriod" class="form-label fw-semibold small text-white">Opening Grace Period (min)</label>
                        <input type="number" min="0" max="600" class="form-control" id="editSbuOpeningGracePeriod" name="opening_grace_period">
                    </div>
                    <div class="col-6">
                        <label for="editSbuClosingGracePeriod" class="form-label fw-semibold small text-white">Closing Grace Period (min)</label>
                        <input type="number" min="0" max="600" class="form-control" id="editSbuClosingGracePeriod" name="closing_grace_period">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="edit_is_active" class="form-label fw-semibold small text-white">
                    Status <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="edit_is_active" name="is_active" required>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </form>
    </div>

    <div class="offcanvas-footer border-top p-3" style="border-color: #ffffffab !important">
        <div class="d-flex justify-content-end align-items-center gap-2">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="button" class="btn btn-light text-dark border-0" id="updateSbuBtn">
                    <i class="bi bi-check-lg me-1"></i>Update SBU
                </button>
            </div>
        </div>
    </div>
</div>
