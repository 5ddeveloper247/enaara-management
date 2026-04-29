(function() {
    'use strict';

    $(document).ready(function() {
        initializeEventHandlers();
    });

    const SBU_LIMITED_FIELDS = [
        { fieldName: 'name', inputId: 'name', lenId: 'sbuNameLen', metaId: 'sbuNameMeta', max: 50 },
        { fieldName: 'city', inputId: 'city', lenId: 'sbuCityLen', metaId: 'sbuCityMeta', max: 50 },
        { fieldName: 'address', inputId: 'address', lenId: 'sbuAddressLen', metaId: 'sbuAddressMeta', max: 255 },
    ];

    const EDIT_SBU_LIMITED_FIELDS = [
        { fieldName: 'name', inputId: 'edit_name', lenId: 'editSbuNameLen', metaId: 'editSbuNameMeta', max: 50 },
        { fieldName: 'city', inputId: 'edit_city', lenId: 'editSbuCityLen', metaId: 'editSbuCityMeta', max: 50 },
        { fieldName: 'address', inputId: 'edit_address', lenId: 'editSbuAddressLen', metaId: 'editSbuAddressMeta', max: 255 },
    ];

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function syncSbuLimitedFieldsState() {
        const form = document.getElementById('addSbuForm');
        if (!form) return;
        SBU_LIMITED_FIELDS.forEach(function (cfg) {
            const el = document.getElementById(cfg.inputId);
            if (!el) return;
            const max = cfg.max;
            if (el.value.length > max) {
                el.value = el.value.substring(0, max);
            }
            const len = el.value.length;
            const lenEl = document.getElementById(cfg.lenId);
            const metaEl = document.getElementById(cfg.metaId);
            if (lenEl) lenEl.textContent = String(len);
            if (metaEl) metaEl.classList.toggle('text-danger', len >= max);

            $(el).siblings('.validation-error-dynamic[data-client-length="1"], .validation-error-dynamic[data-max-reached="1"]').remove();

            if (len === max) {
                const feedback = $('<div class="invalid-feedback d-block validation-error-dynamic" data-error-for="' + cfg.fieldName + '" data-max-reached="1">You cannot enter more than ' + max + ' characters.</div>');
                $(el).after(feedback);
            } else if (!$(el).siblings('.validation-error-dynamic:not([data-client-length]):not([data-max-reached])').length) {
                $(el).removeClass('is-invalid');
            }
        });
        syncSaveSbuButtonState();
    }

    function syncEditSbuLimitedFieldsState() {
        const form = document.getElementById('editSbuForm');
        if (!form) return;
        EDIT_SBU_LIMITED_FIELDS.forEach(function (cfg) {
            const el = document.getElementById(cfg.inputId);
            if (!el) return;
            const max = cfg.max;
            if (el.value.length > max) {
                el.value = el.value.substring(0, max);
            }
            const len = el.value.length;
            const lenEl = document.getElementById(cfg.lenId);
            const metaEl = document.getElementById(cfg.metaId);
            if (lenEl) lenEl.textContent = String(len);
            if (metaEl) metaEl.classList.toggle('text-danger', len >= max);

            $(el).siblings('.validation-error-dynamic[data-client-length="1"], .validation-error-dynamic[data-max-reached="1"]').remove();

            if (len === max) {
                const feedback = $('<div class="invalid-feedback d-block validation-error-dynamic" data-error-for="' + cfg.fieldName + '" data-max-reached="1">You cannot enter more than ' + max + ' characters.</div>');
                $(el).after(feedback);
            } else if (!$(el).siblings('.validation-error-dynamic:not([data-client-length]):not([data-max-reached])').length) {
                $(el).removeClass('is-invalid');
            }
        });
        syncUpdateSbuButtonState();
    }

    function syncAddSbuScheduleRadiosRequired() {
        const orgVal = ($('#organization_id').val() || '').trim();
        const radios = document.querySelectorAll('#addSbuForm input[name="schedule_mode"]');
        radios.forEach(function (r) {
            r.required = false;
        });
        if (orgVal && radios.length) {
            radios[0].required = true;
        }
    }

    function syncEditSbuScheduleRadiosRequired() {
        const orgVal = ($('#edit_organization_id').val() || '').trim();
        const radios = document.querySelectorAll('#editSbuForm input[name="schedule_mode"]');
        radios.forEach(function (r) {
            r.required = false;
        });
        if (orgVal && radios.length) {
            radios[0].required = true;
        }
    }

    function syncSaveSbuButtonState() {
        const form = document.getElementById('addSbuForm');
        const btn = document.getElementById('saveSbuBtn');
        if (!form || !btn) return;
        syncAddSbuScheduleRadiosRequired();
        const isValid = form.checkValidity();
        const hasClientErrors = $(form).find('.validation-error-dynamic[data-client-length="1"]').length > 0;
        btn.disabled = !isValid || hasClientErrors;
    }

    function syncUpdateSbuButtonState() {
        const form = document.getElementById('editSbuForm');
        const btn = document.getElementById('updateSbuBtn');
        if (!form || !btn) return;
        syncEditSbuScheduleRadiosRequired();
        const isValid = form.checkValidity();
        const hasClientErrors = $(form).find('.validation-error-dynamic[data-client-length="1"]').length > 0;
        btn.disabled = !isValid || hasClientErrors;
    }

    function applyFilters() {
        const status = $('input[name="filterStatus"]:checked').val();

        $('#sbusGrid .col-md-6').each(function() {
            const card = $(this).find('.sbu-card');
            const cardStatus = String(card.data('sbu-status'));

            if (status === 'all' || cardStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function clearFilters() {
        $('input#filterStatusAll').prop('checked', true);
        $('#sbusGrid .col-md-6').show();
    }

    function populateDetailCanvas(button) {
        const get = function(attr, fallback) {
            const v = button.getAttribute(attr);
            return (v !== null && v !== '') ? v : (fallback || '—');
        };

        const name = get('data-sbu-name');

        $('#detailSbuLogoPlaceholder').text((name.substring(0, 2) || '?').toUpperCase());
        $('#detailSbuName').text(name);
        $('#detailSbuCity').text(get('data-sbu-city'));
        $('#detailSbuOrganization').text(get('data-organization-name'));
        $('#detailSbuAddress').text(get('data-sbu-address'));

        const lat = button.getAttribute('data-sbu-latitude');
        const lng = button.getAttribute('data-sbu-longitude');

        $('#detailSbuCoordinates').text((lat && lng) ? lat + ', ' + lng : '—');
        $('#detailSbuStatus').text(get('data-sbu-active') === '1' ? 'Active' : 'Inactive');
    }

    function clearFormMessages(formSelector) {
        $(formSelector + ' .is-invalid').removeClass('is-invalid');
        $(formSelector + ' .btn-group.is-invalid').removeClass('is-invalid border border-danger rounded-2');
        $(formSelector + ' .invalid-feedback').remove();
        $(formSelector + ' .form-alert-box').remove();
        $(formSelector + ' .validation-error-dynamic').remove();
    }

    function showFormMessage(formSelector, message, type = 'danger') {
        $(formSelector + ' .form-alert-box').remove();

        const html = `
            <div class="alert alert-${type} form-alert-box mt-2 mb-3" role="alert">
                ${message}
            </div>
        `;

        $(formSelector).prepend(html);
    }

    function resetAddSbuForm() {
        const form = document.getElementById('addSbuForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#addSbuForm');
        $('#is_active').val('1');
        $('#sbuScheduleModeStandard, #sbuScheduleModeCustom').prop('checked', false);
        toggleAddSbuScheduleMode();
        syncSbuLimitedFieldsState();
    }

    function resetEditSbuForm() {
        const form = document.getElementById('editSbuForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#editSbuForm');
        $('#edit_is_active').val('1');
        $('#editSbuForm').attr('data-update-url', '');
        $('#deleteSbuBtn').attr('data-delete-url', '');
        $('#editSbuScheduleModeStandard, #editSbuScheduleModeCustom').prop('checked', false);
        toggleEditSbuScheduleMode();
        syncEditSbuLimitedFieldsState();
    }

    function getAddSelectedOrganizationOption() {
        const select = document.getElementById('organization_id');
        if (!select) return null;
        return select.options[select.selectedIndex] || null;
    }

    function getEditSelectedOrganizationOption() {
        const select = document.getElementById('edit_organization_id');
        if (!select) return null;
        return select.options[select.selectedIndex] || null;
    }

    function getScheduleFromOption(option) {
        if (!option || !option.value) {
            return {
                workingDays: [],
                workingStartTime: '',
                workingEndTime: '',
                gracePeriod: ''
            };
        }
        const openingGracePeriod = option.dataset.openingGracePeriod || '';
        const closingGracePeriod = option.dataset.closingGracePeriod || '';
        return {
            workingDays: (option.dataset.workingDays || '').split(',').filter(Boolean),
            workingStartTime: option.dataset.workingStartTime || '',
            workingEndTime: option.dataset.workingEndTime || '',
            gracePeriod: openingGracePeriod || closingGracePeriod
        };
    }

    function applyAddOrganizationSchedule() {
        const option = getAddSelectedOrganizationOption();
        const schedule = getScheduleFromOption(option);
        $('.sbu-working-day').each(function() {
            this.checked = schedule.workingDays.includes(this.value);
        });
        $('#sbuWorkingStartTime').val(schedule.workingStartTime);
        $('#sbuWorkingEndTime').val(schedule.workingEndTime);
        $('#sbuGracePeriod').val(schedule.gracePeriod);
    }

    function toggleAddSbuScheduleMode() {
        const hasOrganization = ($('#organization_id').val() || '') !== '';
        if (!hasOrganization) {
            $('#sbuScheduleModeSection').addClass('d-none');
            $('#sbuWorkingScheduleFields').removeClass('pe-none opacity-50');
            return;
        }
        $('#sbuScheduleModeSection').removeClass('d-none');
        if ($('#sbuScheduleModeStandard').is(':checked')) {
            applyAddOrganizationSchedule();
            $('#sbuWorkingScheduleFields').addClass('pe-none opacity-50');
        } else {
            $('#sbuWorkingScheduleFields').removeClass('pe-none opacity-50');
        }
    }

    function applyEditOrganizationSchedule() {
        const option = getEditSelectedOrganizationOption();
        const schedule = getScheduleFromOption(option);
        $('.edit-sbu-working-day').each(function() {
            this.checked = schedule.workingDays.includes(this.value);
        });
        $('#editSbuWorkingStartTime').val(schedule.workingStartTime);
        $('#editSbuWorkingEndTime').val(schedule.workingEndTime);
        $('#editSbuGracePeriod').val(schedule.gracePeriod);
    }

    function schedulesMatchParentForEdit(currentWorkingDays, currentStartTime, currentEndTime, currentGracePeriod) {
        const option = getEditSelectedOrganizationOption();
        const schedule = getScheduleFromOption(option);
        const current = [...currentWorkingDays].sort().join(',');
        const parent = [...schedule.workingDays].sort().join(',');
        return current === parent
            && (currentStartTime || '') === schedule.workingStartTime
            && (currentEndTime || '') === schedule.workingEndTime
            && (currentGracePeriod || '') === schedule.gracePeriod;
    }

    function toggleEditSbuScheduleMode() {
        const hasOrganization = ($('#edit_organization_id').val() || '') !== '';
        if (!hasOrganization) {
            $('#editSbuScheduleModeSection').addClass('d-none');
            $('#editSbuWorkingScheduleFields').removeClass('pe-none opacity-50');
            return;
        }
        $('#editSbuScheduleModeSection').removeClass('d-none');
        if ($('#editSbuScheduleModeStandard').is(':checked')) {
            applyEditOrganizationSchedule();
            $('#editSbuWorkingScheduleFields').addClass('pe-none opacity-50');
        } else {
            $('#editSbuWorkingScheduleFields').removeClass('pe-none opacity-50');
        }
    }

    function showValidationErrors(formSelector, errors) {
        clearFormMessages(formSelector);

        $.each(errors, function(field, messages) {
            const normalizedField = String(field).replace(/\.\d+$/, '');
            const message = Array.isArray(messages) ? messages[0] : messages;
            if (!message) {
                return;
            }

            if (normalizedField === 'working_days') {
                const checkboxes = $(formSelector + ' input[name="working_days[]"]');
                if (checkboxes.length) {
                    checkboxes.addClass('is-invalid');
                    const wrapper = checkboxes.first().closest('.mb-3');
                    if (!wrapper.find('[data-error-for="working_days"]').length) {
                        wrapper.append('<div class="invalid-feedback d-block validation-error-dynamic" data-error-for="working_days">' + message + '</div>');
                    }
                }
                return;
            }

            if (normalizedField === 'schedule_mode') {
                const section = $(formSelector + ' [id$="SbuScheduleModeSection"]');
                const group = section.find('.btn-group[aria-label="Selection Mode"]');
                if (group.length) {
                    group.addClass('is-invalid border border-danger rounded-2');
                    if (!section.find('[data-error-for="schedule_mode"]').length) {
                        group.after('<div class="invalid-feedback d-block validation-error-dynamic text-white" data-error-for="schedule_mode">' + message + '</div>');
                    }
                }
                return;
            }

            const input = $(formSelector + ' [name="' + normalizedField + '"], ' + formSelector + ' [name="' + normalizedField + '[]"]');
            if (input.length) {
                input.first().addClass('is-invalid');
                if ($(formSelector + ' [data-error-for="' + normalizedField + '"]').length === 0) {
                    input.first().after('<div class="invalid-feedback d-block validation-error-dynamic" data-error-for="' + normalizedField + '">' + message + '</div>');
                }
            }
        });

        const firstInvalid = $(formSelector + ' .is-invalid').first();
        if (firstInvalid.length) {
            firstInvalid.trigger('focus');
        }
        
        // Re-sync counters to ensure character limit feedback is preserved if any
        if (formSelector === '#addSbuForm') syncSbuLimitedFieldsState();
        else syncEditSbuLimitedFieldsState();
    }

    function storeSbu() {
        const form = $('#addSbuForm');
        const url = form.data('store-url');

        clearFormMessages('#addSbuForm');

        if (!url) {
            showFormMessage('#addSbuForm', 'Store URL not found.');
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            beforeSend: function() {
                $('#saveSbuBtn')
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('addSbuCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    if (window.showSuccess) {
                        window.showSuccess(response.message || 'SBU created successfully.').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'SBU created successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                } else {
                    showFormMessage('#addSbuForm', response.message || 'Failed to create SBU.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#addSbuForm', xhr.responseJSON.errors);
                } else {
                    if (window.showError) {
                        window.showError((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to create SBU.');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'System Error',
                            text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to create SBU.'
                        });
                    }
                }
            },
            complete: function() {
                $('#saveSbuBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Create SBU');
                syncSaveSbuButtonState();
            }
        });
    }

    function loadEditSbuData(button) {
        const editUrl = $(button).data('edit-url');

        clearFormMessages('#editSbuForm');

        if (!editUrl) {
            showFormMessage('#editSbuForm', 'Edit URL not found.');
            return;
        }

        resetEditSbuForm();

        $.ajax({
            url: editUrl,
            type: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;

                    $('#edit_id').val(data.id ?? '');
                    $('#edit_organization_id').val(data.organization_id ?? '');
                    $('#edit_name').val(data.name ?? '');
                    $('#edit_city').val(data.city ?? '');
                    $('#edit_address').val(data.address ?? '');
                    $('#edit_latitude').val(data.latitude ?? '');
                    $('#edit_longitude').val(data.longitude ?? '');
                    const editWorkingDays = Array.isArray(data.working_days) ? data.working_days : [];
                    $('.edit-sbu-working-day').each(function() {
                        this.checked = editWorkingDays.includes(this.value);
                    });
                    const editStartTime = (data.working_start_time ?? '').toString().slice(0, 5);
                    const editEndTime = (data.working_end_time ?? '').toString().slice(0, 5);
                    $('#editSbuWorkingStartTime').val(editStartTime);
                    $('#editSbuWorkingEndTime').val(editEndTime);
                    const editGracePeriod = (data.opening_grace_period ?? data.closing_grace_period ?? '').toString();
                    $('#editSbuGracePeriod').val(editGracePeriod);
                    $('#edit_is_active').val(
                        data.is_active === 1 || data.is_active === '1' || data.is_active === true ? '1' : '0'
                    );
                    if ((data.organization_id ?? '') !== '') {
                        if (schedulesMatchParentForEdit(editWorkingDays, editStartTime, editEndTime, editGracePeriod)) {
                            $('#editSbuScheduleModeStandard').prop('checked', true);
                        } else {
                            $('#editSbuScheduleModeCustom').prop('checked', true);
                        }
                    } else {
                        $('#editSbuScheduleModeCustom').prop('checked', true);
                    }
                    toggleEditSbuScheduleMode();

                    $('#editSbuForm').attr('data-update-url', $(button).data('update-url'));
                    $('#deleteSbuBtn').attr('data-delete-url', $(button).data('delete-url'));
                    
                    syncEditSbuLimitedFieldsState();
                } else {
                    showFormMessage('#editSbuForm', response.message || 'Failed to load SBU data.');
                }
            },
            error: function(xhr) {
                showFormMessage(
                    '#editSbuForm',
                    (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Failed to fetch SBU.'
                );
            }
        });
    }

    function updateSbu() {
        const form = $('#editSbuForm');
        const url = form.attr('data-update-url');

        clearFormMessages('#editSbuForm');

        if (!url) {
            showFormMessage('#editSbuForm', 'Update URL not found.');
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            beforeSend: function() {
                $('#updateSbuBtn')
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('editSbuCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    if (window.showSuccess) {
                        window.showSuccess(response.message || 'SBU updated successfully.').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'SBU updated successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                } else {
                    showFormMessage('#editSbuForm', response.message || 'Failed to update SBU.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#editSbuForm', xhr.responseJSON.errors);
                } else {
                    if (window.showError) {
                        window.showError((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update SBU.');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'System Error',
                            text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update SBU.'
                        });
                    }
                }
            },
            complete: function() {
                $('#updateSbuBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Update SBU');
                syncUpdateSbuButtonState();
            }
        });
    }

    function deleteSbu(button) {
        const deleteUrl = $(button).data('delete-url');

        if (!deleteUrl) {
            showFormMessage('#editSbuForm', 'Delete URL not found.');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'This SBU will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: deleteUrl,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: getCsrfToken()
                },
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                beforeSend: function() {
                    $('#deleteSbuBtn')
                        .prop('disabled', true)
                        .html('<i class="bi bi-trash me-1"></i>Deleting...');
                },
                success: function(response) {
                    if (response.success) {
                        const canvasEl = document.getElementById('editSbuCanvas');
                        const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                        if (offcanvas) {
                            offcanvas.hide();
                        }

                        if (window.showSuccess) {
                            window.showSuccess(response.message || 'SBU deleted successfully.', 'Deleted').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: response.message || 'SBU deleted successfully.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        }
                    } else {
                        showFormMessage('#editSbuForm', response.message || 'Failed to delete SBU.');
                    }
                },
                error: function(xhr) {
                    if (window.showError) {
                        window.showError((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete SBU.');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'System Error',
                            text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete SBU.'
                        });
                    }
                },
                complete: function() {
                    $('#deleteSbuBtn')
                        .prop('disabled', false)
                        .html('<i class="bi bi-trash me-1"></i>Delete');
                }
            });
        });
    }

    function initializeEventHandlers() {
        const sbuDetailCanvas = document.getElementById('sbuDetailCanvas');
        if (sbuDetailCanvas) {
            sbuDetailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('view-sbu-btn')) {
                    populateDetailCanvas(button);
                }
            });
        }

        const addSbuCanvas = document.getElementById('addSbuCanvas');
        if (addSbuCanvas) {
            addSbuCanvas.addEventListener('show.bs.offcanvas', function() {
                resetAddSbuForm();
            });
            addSbuCanvas.addEventListener('shown.bs.offcanvas', function () {
                syncSbuLimitedFieldsState();
            });
        }

        const editSbuCanvas = document.getElementById('editSbuCanvas');
        if (editSbuCanvas) {
            editSbuCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('edit-sbu-btn')) {
                    loadEditSbuData(button);
                }
            });

            editSbuCanvas.addEventListener('hidden.bs.offcanvas', function() {
                resetEditSbuForm();
            });
            
            editSbuCanvas.addEventListener('shown.bs.offcanvas', function () {
                syncEditSbuLimitedFieldsState();
            });
        }

        $('.filter-status').on('change', function() {
            applyFilters();
        });

        $('#clearFiltersBtn').on('click', function() {
            clearFilters();
        });

        $(document).on('click', '#saveSbuBtn', function(e) {
            e.preventDefault();
            storeSbu();
        });

        $('#organization_id').on('change', function() {
            if (!this.value) {
                $('#sbuScheduleModeStandard, #sbuScheduleModeCustom').prop('checked', false);
            }
            toggleAddSbuScheduleMode();
            syncSaveSbuButtonState();
        });
        $('#sbuScheduleModeStandard, #sbuScheduleModeCustom').on('change', function() {
            $('#addSbuForm .btn-group[aria-label="Selection Mode"]').removeClass('is-invalid border border-danger rounded-2');
            $('#addSbuForm [data-error-for="schedule_mode"]').remove();
            toggleAddSbuScheduleMode();
            syncSaveSbuButtonState();
        });

        $(document).on('click', '#updateSbuBtn', function(e) {
            e.preventDefault();
            updateSbu();
        });

        $('#edit_organization_id').on('change', function() {
            if (!this.value) {
                $('#editSbuScheduleModeStandard, #editSbuScheduleModeCustom').prop('checked', false);
            }
            toggleEditSbuScheduleMode();
            syncUpdateSbuButtonState();
        });
        $('#editSbuScheduleModeStandard, #editSbuScheduleModeCustom').on('change', function() {
            $('#editSbuForm .btn-group[aria-label="Selection Mode"]').removeClass('is-invalid border border-danger rounded-2');
            $('#editSbuForm [data-error-for="schedule_mode"]').remove();
            toggleEditSbuScheduleMode();
            syncUpdateSbuButtonState();
        });

        $(document).on('click', '.delete-sbu-btn', function(e) {
            e.preventDefault();
            deleteSbu(this);
        });

        // Add real-time validation listeners
        const addSbuForm = document.getElementById('addSbuForm');
        if (addSbuForm) {
            addSbuForm.querySelectorAll('input, select, textarea').forEach(function (el) {
                el.addEventListener('change', syncSbuLimitedFieldsState);
                el.addEventListener('input', syncSbuLimitedFieldsState);
            });
        }

        const editSbuForm = document.getElementById('editSbuForm');
        if (editSbuForm) {
            editSbuForm.querySelectorAll('input, select, textarea').forEach(function (el) {
                el.addEventListener('change', syncEditSbuLimitedFieldsState);
                el.addEventListener('input', syncEditSbuLimitedFieldsState);
            });
        }

        toggleAddSbuScheduleMode();
        toggleEditSbuScheduleMode();
        syncSbuLimitedFieldsState();
        syncEditSbuLimitedFieldsState();
    }
})();