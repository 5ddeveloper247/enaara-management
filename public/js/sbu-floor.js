(function() {
    'use strict';

    $(document).ready(function() {
        initializeEventHandlers();
    });

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    const FLOOR_LIMITED_FIELDS = [
        { fieldName: 'name', inputId: 'name', lenId: 'floorNameLen', metaId: 'floorNameMeta', max: 50 },
        { fieldName: 'floor_number', inputId: 'floor_number', lenId: 'floorNumberLen', metaId: 'floorNumberMeta', max: 50 }
    ];

    const EDIT_FLOOR_LIMITED_FIELDS = [
        { fieldName: 'name', inputId: 'edit_name', lenId: 'editFloorNameLen', metaId: 'editFloorNameMeta', max: 50 },
        { fieldName: 'floor_number', inputId: 'edit_floor_number', lenId: 'editFloorNumberLen', metaId: 'editFloorNumberMeta', max: 50 }
    ];

    function syncSaveFloorButtonState() {
        const form = document.getElementById('addSbuFloorForm');
        const btn = document.getElementById('saveSbuFloorBtn');
        if (!form || !btn) return;
        const isValid = form.checkValidity();
        const hasClientErrors = $(form).find('.validation-error-dynamic[data-client-length="1"]').length > 0;
        btn.disabled = !isValid || hasClientErrors;
    }

    function syncUpdateFloorButtonState() {
        const form = document.getElementById('editSbuFloorForm');
        const btn = document.getElementById('updateSbuFloorBtn');
        if (!form || !btn) return;
        const isValid = form.checkValidity();
        const hasClientErrors = $(form).find('.validation-error-dynamic[data-client-length="1"]').length > 0;
        btn.disabled = !isValid || hasClientErrors;
    }

    function syncFloorLimitedFieldsState() {
        const form = document.getElementById('addSbuFloorForm');
        if (!form) return;
        FLOOR_LIMITED_FIELDS.forEach(function(cfg) {
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
                $(el).addClass('is-invalid');
                $(el).after('<div class="invalid-feedback d-block validation-error-dynamic" data-error-for="' + cfg.fieldName + '" data-max-reached="1">You cannot enter more than ' + max + ' characters.</div>');
            } else if (!$(el).siblings('.validation-error-dynamic:not([data-client-length]):not([data-max-reached])').length) {
                $(el).removeClass('is-invalid');
            }
        });
        syncSaveFloorButtonState();
    }

    function syncEditFloorLimitedFieldsState() {
        const form = document.getElementById('editSbuFloorForm');
        if (!form) return;
        EDIT_FLOOR_LIMITED_FIELDS.forEach(function(cfg) {
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
                $(el).addClass('is-invalid');
                $(el).after('<div class="invalid-feedback d-block validation-error-dynamic" data-error-for="' + cfg.fieldName + '" data-max-reached="1">You cannot enter more than ' + max + ' characters.</div>');
            } else if (!$(el).siblings('.validation-error-dynamic:not([data-client-length]):not([data-max-reached])').length) {
                $(el).removeClass('is-invalid');
            }
        });
        syncUpdateFloorButtonState();
    }

    function ucfirst(str) {
        if (!str) return '—';
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }

    function applyFilters() {
        const status = $('input[name="filterStatus"]:checked').val();

        $('#sbuFloorsGrid .col-md-6').each(function() {
            const card = $(this).find('.sbu-floor-card');
            const cardStatus = String(card.data('floor-status'));

            if (status === 'all' || cardStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function clearFilters() {
        $('input#filterStatusAll').prop('checked', true);
        $('#sbuFloorsGrid .col-md-6').show();
    }

    function renderBiometricRowsInDetail(devices) {
        const $bioList = $('#detailFloorBiometricList');
        $bioList.empty();

        if (!Array.isArray(devices) || devices.length === 0) {
            $bioList.append(
                $('<li>', {
                    class: 'list-group-item bg-transparent text-white border-0 px-3 py-2 small opacity-75',
                    text: 'No biometric devices assigned to this floor.'
                })
            );
            return;
        }

        devices.forEach(function(d, idx) {
            const line = (d.device_name || 'Device') + ' · ' + (d.serial_number || String(d.id || '')) + ' · ID ' + String(d.id || '');
            const isLast = idx === devices.length - 1;
            $bioList.append(
                $('<li>', {
                    class: 'list-group-item bg-transparent text-white border-0 px-3 py-2 small',
                    css: isLast ? {} : { borderBottom: '1px solid rgba(255,255,255,0.12)' },
                    text: line
                })
            );
        });
    }

    function populateDetailCanvasFromPayload(data) {
        const name = data.name || '—';

        const initials = name !== '—'
            ? name.split(' ').map(word => word.charAt(0)).join('').substring(0, 2).toUpperCase()
            : 'F';
        $('#detailFloorLogoPlaceholder').text(initials);
        $('#detailFloorName').text(name);
        $('#detailFloorType').text(ucfirst(data.floor_type || ''));
        $('#detailFloorOrganizationName').text(data.organization_name || '—');
        $('#detailFloorSbuName').text(data.sbu_name || '—');
        $('#detailFloorNumber').text(data.floor_number !== null && data.floor_number !== undefined && String(data.floor_number) !== '' ? String(data.floor_number) : '—');
        $('#detailFloorRestricted').text(data.is_restricted === true || data.is_restricted === 1 || data.is_restricted === '1' ? 'Yes' : 'No');
        $('#detailFloorStatus').text(data.is_active === true || data.is_active === 1 || data.is_active === '1' ? 'Active' : 'Inactive');
        renderBiometricRowsInDetail(data.biometric_devices || []);
    }

    function loadFloorDetailIntoCanvas(triggerEl) {
        const btn = triggerEl && triggerEl.closest ? triggerEl.closest('.view-floor-btn') : null;

        if (!btn) {
            return;
        }

        const url = btn.getAttribute('data-detail-url');

        if (!url) {
            return;
        }

        $('#detailFloorName').text('…');
        $('#detailFloorOrganizationName').text('…');
        $('#detailFloorSbuName').text('…');
        $('#detailFloorNumber').text('…');
        $('#detailFloorType').text('…');
        $('#detailFloorRestricted').text('…');
        $('#detailFloorStatus').text('…');
        $('#detailFloorBiometricList').html(
            '<li class="list-group-item bg-transparent text-white border-0 px-3 py-2 small opacity-75">Loading…</li>'
        );

        $.ajax({
            url: url,
            type: 'GET',
            headers: { Accept: 'application/json' },
            success: function(response) {
                if (response.success && response.data) {
                    populateDetailCanvasFromPayload(response.data);
                } else {
                    $('#detailFloorBiometricList').html(
                        '<li class="list-group-item bg-transparent text-white border-0 px-3 py-2 small text-warning">Could not load floor details.</li>'
                    );
                }
            },
            error: function() {
                $('#detailFloorBiometricList').html(
                    '<li class="list-group-item bg-transparent text-white border-0 px-3 py-2 small text-warning">Could not load floor details.</li>'
                );
            }
        });
    }

    function clearFormMessages(formSelector) {
        $(formSelector + ' .is-invalid').removeClass('is-invalid');
        $(formSelector + ' .invalid-feedback').remove();
        $(formSelector + ' .form-alert-box').remove();
        $(formSelector + ' .validation-error-dynamic').remove();
        $('#add_floor_biometric_section, #edit_floor_biometric_section').removeClass('border border-danger');
        $('#add_biometric_devices_feedback, #edit_biometric_devices_feedback').remove();
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

    function showValidationErrors(formSelector, errors) {
        clearFormMessages(formSelector);
        let firstInvalid = null;

        $.each(errors, function(field, messages) {
            if (String(field).indexOf('biometric_device_ids') === 0) {
                const section = formSelector === '#editSbuFloorForm' ? '#edit_floor_biometric_section' : '#add_floor_biometric_section';
                $(section).addClass('border border-danger');
                const fbId = formSelector === '#editSbuFloorForm' ? 'edit_biometric_devices_feedback' : 'add_biometric_devices_feedback';
                $('#' + fbId).remove();
                $(section).append(
                    '<div class="invalid-feedback d-block text-white" id="' + fbId + '">' + messages[0] + '</div>'
                );
                return;
            }

            let input = $(formSelector + ' [name="' + field + '"]');
            if (!input.length && field.includes('.')) {
                const root = field.split('.')[0];
                input = $(formSelector + ' [name="' + root + '"]');
            }

            if (!input.length && field === 'organization_id') {
                input = $(formSelector + ' #organization_id, ' + formSelector + ' #edit_organization_id');
            }

            if (input.length) {
                const normalizedField = String(field).replace(/\.\d+$/, '');
                const firstMessage = Array.isArray(messages) ? messages[0] : messages;
                input.first().addClass('is-invalid');
                if ($(formSelector + ' [data-error-for="' + normalizedField + '"]').length === 0) {
                    input.first().after('<div class="invalid-feedback d-block validation-error-dynamic" data-error-for="' + normalizedField + '">' + firstMessage + '</div>');
                }
                if (!firstInvalid) {
                    firstInvalid = input.first();
                }
            }
        });

        if (firstInvalid && firstInvalid.length) {
            firstInvalid.trigger('focus');
        }

        if (formSelector === '#addSbuFloorForm') syncFloorLimitedFieldsState();
        else syncEditFloorLimitedFieldsState();
    }

    function getAllSbus() {
        return Array.isArray(window.sbuFloorSbus) ? window.sbuFloorSbus : [];
    }

    function getBiometricDevices() {
        return Array.isArray(window.sbuFloorBiometricDevices) ? window.sbuFloorBiometricDevices : [];
    }

    function renderBiometricCheckboxList(containerSelector, sbuId, checkedIds, formPrefix) {
        const $box = $(containerSelector);
        $box.empty();

        const sbu = String(sbuId || '');
        if (!sbu) {
            $box.append('<p class="small text-white-50 mb-0">Select SBU to list biometric devices.</p>');
            return;
        }

        const list = getBiometricDevices().filter(function(d) {
            return String(d.sbu_id) === sbu;
        });

        if (!list.length) {
            $box.append('<p class="small text-white-50 mb-0">No biometric devices registered for this SBU.</p>');
            return;
        }

        const checkedSet = new Set((checkedIds || []).map(function(id) {
            return String(id);
        }));

        list.forEach(function(d) {
            const id = String(d.id);
            const inputId = formPrefix + '_bio_' + id;
            const labelText = (d.device_name || 'Device') + ' · ' + (d.serial_number || id);
            const $div = $('<div class="form-check mb-2">');
            const $input = $('<input>', {
                type: 'checkbox',
                class: 'form-check-input',
                name: 'biometric_device_ids[]',
                value: id,
                id: inputId
            });
            if (checkedSet.has(id)) {
                $input.prop('checked', true);
            }
            const $label = $('<label>', { class: 'form-check-label small', for: inputId }).text(labelText);
            $div.append($input, $label);
            $box.append($div);
        });
    }

    function refreshAddFloorBiometricList() {
        const sbuId = $('#sbu_id').val();
        const $sec = $('#add_floor_biometric_section');

        if (!sbuId) {
            $sec.addClass('d-none');
            $('#add_floor_biometric_list').empty();
            return;
        }

        $sec.removeClass('d-none');
        renderBiometricCheckboxList('#add_floor_biometric_list', sbuId, [], 'add');
    }

    function refreshEditFloorBiometricList(checkedIds) {
        const sbuId = $('#edit_sbu_id').val();
        const $sec = $('#edit_floor_biometric_section');

        if (!sbuId) {
            $sec.addClass('d-none');
            $('#edit_floor_biometric_list').empty();
            return;
        }

        $sec.removeClass('d-none');
        renderBiometricCheckboxList('#edit_floor_biometric_list', sbuId, checkedIds || [], 'edit');
    }

    function setSbuOptions(selectSelector, organizationId, placeholder) {
        const sbuSelect = $(selectSelector);
        const allSbus = getAllSbus();
        const orgId = String(organizationId || '');

        if (!orgId) {
            sbuSelect.prop('disabled', true);
            sbuSelect.html('<option value="" hidden selected>' + (placeholder || 'First select organization') + '</option>');
            return;
        }

        const filtered = allSbus.filter(function(sbu) {
            return String(sbu.organization_id) === orgId;
        });

        let options = '<option value="" hidden selected>Select SBU</option>';
        filtered.forEach(function(sbu) {
            options += '<option value="' + sbu.id + '">' + sbu.name + '</option>';
        });

        sbuSelect.prop('disabled', false);
        sbuSelect.html(options);
    }

    function resetAddFloorForm() {
        const form = document.getElementById('addSbuFloorForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#addSbuFloorForm');
        $('#organization_id').val('');
        setSbuOptions('#sbu_id', null, 'First select organization');
        $('#floor_type').val('operational');
        $('#is_restricted').val('0');
        $('#is_active').val('1');
        refreshAddFloorBiometricList();
        syncFloorLimitedFieldsState();
    }

    function resetEditFloorForm() {
        const form = document.getElementById('editSbuFloorForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#editSbuFloorForm');
        $('#edit_organization_id').val('');
        setSbuOptions('#edit_sbu_id', null, 'First select organization');
        $('#edit_floor_type').val('operational');
        $('#edit_is_restricted').val('0');
        $('#edit_is_active').val('1');
        $('#editSbuFloorForm').attr('data-update-url', '');
        $('#deleteSbuFloorBtn').attr('data-delete-url', '');
        refreshEditFloorBiometricList([]);
        syncEditFloorLimitedFieldsState();
    }

    function storeFloor() {
        const form = $('#addSbuFloorForm');
        const url = form.data('store-url');

        clearFormMessages('#addSbuFloorForm');

        if (!url) {
            showFormMessage('#addSbuFloorForm', 'Store URL not found.');
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
                $('#saveSbuFloorBtn')
                    .prop('disabled', true)
                    .html('Saving...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('addSbuFloorCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'SBU floor created successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showFormMessage('#addSbuFloorForm', response.message || 'Failed to create SBU floor.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#addSbuFloorForm', xhr.responseJSON.errors);
                } else {
                    showFormMessage('#addSbuFloorForm', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to create SBU floor.');
                }
            },
            complete: function() {
                $('#saveSbuFloorBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Create Floor');
                syncSaveFloorButtonState();
            }
        });
    }

    function loadEditFloorData(button) {
        const editUrl = $(button).data('edit-url');

        clearFormMessages('#editSbuFloorForm');

        if (!editUrl) {
            showFormMessage('#editSbuFloorForm', 'Edit URL not found.');
            return;
        }

        resetEditFloorForm();

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
                    setSbuOptions('#edit_sbu_id', data.organization_id ?? null, 'First select organization');
                    $('#edit_sbu_id').val(data.sbu_id ?? '');
                    $('#edit_name').val(data.name ?? '');
                    $('#edit_floor_number').val(data.floor_number ?? '');
                    $('#edit_floor_type').val(data.floor_type ?? 'operational');
                    $('#edit_is_restricted').val(
                        data.is_restricted === 1 || data.is_restricted === '1' || data.is_restricted === true ? '1' : '0'
                    );
                    $('#edit_is_active').val(
                        data.is_active === 1 || data.is_active === '1' || data.is_active === true ? '1' : '0'
                    );

                    $('#editSbuFloorForm').attr('data-update-url', $(button).data('update-url'));
                    $('#deleteSbuFloorBtn').attr('data-delete-url', $(button).data('delete-url'));
                    refreshEditFloorBiometricList(data.biometric_device_ids || []);
                    syncEditFloorLimitedFieldsState();
                } else {
                    showFormMessage('#editSbuFloorForm', response.message || 'Failed to load floor data.');
                }
            },
            error: function(xhr) {
                showFormMessage(
                    '#editSbuFloorForm',
                    (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Failed to fetch floor.'
                );
            }
        });
    }

    function updateFloor() {
        const form = $('#editSbuFloorForm');
        const url = form.attr('data-update-url');

        clearFormMessages('#editSbuFloorForm');

        if (!url) {
            showFormMessage('#editSbuFloorForm', 'Update URL not found.');
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
                $('#updateSbuFloorBtn')
                    .prop('disabled', true)
                    .html('Updating...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('editSbuFloorCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'SBU floor updated successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showFormMessage('#editSbuFloorForm', response.message || 'Failed to update SBU floor.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#editSbuFloorForm', xhr.responseJSON.errors);
                } else {
                    showFormMessage('#editSbuFloorForm', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update SBU floor.');
                }
            },
            complete: function() {
                $('#updateSbuFloorBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Update Floor');
                syncUpdateFloorButtonState();
            }
        });
    }

    function deleteFloor(button) {
        const deleteUrl = $(button).data('delete-url');

        if (!deleteUrl) {
            showFormMessage('#editSbuFloorForm', 'Delete URL not found.');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'This SBU floor will be permanently deleted.',
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
                    $('#deleteSbuFloorBtn')
                        .prop('disabled', true)
                        .html('<i class="bi bi-trash me-1"></i>Deleting...');
                },
                success: function(response) {
                    if (response.success) {
                        const canvasEl = document.getElementById('editSbuFloorCanvas');
                        const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                        if (offcanvas) {
                            offcanvas.hide();
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: response.message || 'SBU floor deleted successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        showFormMessage('#editSbuFloorForm', response.message || 'Failed to delete SBU floor.');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete SBU floor.'
                    });
                },
                complete: function() {
                    $('#deleteSbuFloorBtn')
                        .prop('disabled', false)
                        .html('<i class="bi bi-trash me-1"></i>Delete');
                }
            });
        });
    }

    function initializeEventHandlers() {
        const floorDetailCanvas = document.getElementById('sbuFloorDetailCanvas');
        if (floorDetailCanvas) {
            floorDetailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const raw = event.relatedTarget;
                loadFloorDetailIntoCanvas(raw);
            });
        }

        const addFloorCanvas = document.getElementById('addSbuFloorCanvas');
        if (addFloorCanvas) {
            addFloorCanvas.addEventListener('show.bs.offcanvas', function() {
                resetAddFloorForm();
            });
        }

        const editFloorCanvas = document.getElementById('editSbuFloorCanvas');
        if (editFloorCanvas) {
            editFloorCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('edit-floor-btn')) {
                    loadEditFloorData(button);
                }
            });

            editFloorCanvas.addEventListener('hidden.bs.offcanvas', function() {
                resetEditFloorForm();
            });
        }

        $('.filter-status').on('change', function() {
            applyFilters();
        });

        $('#organization_id').on('change', function() {
            setSbuOptions('#sbu_id', $(this).val(), 'First select organization');
            refreshAddFloorBiometricList();
            syncSaveFloorButtonState();
        });

        $('#sbu_id').on('change', function() {
            refreshAddFloorBiometricList();
            syncSaveFloorButtonState();
        });

        $('#edit_organization_id').on('change', function() {
            setSbuOptions('#edit_sbu_id', $(this).val(), 'First select organization');
            refreshEditFloorBiometricList([]);
            syncUpdateFloorButtonState();
        });

        $('#edit_sbu_id').on('change', function() {
            refreshEditFloorBiometricList([]);
            syncUpdateFloorButtonState();
        });

        $('#clearFiltersBtn').on('click', function() {
            clearFilters();
        });

        $(document).on('click', '#saveSbuFloorBtn', function(e) {
            e.preventDefault();
            storeFloor();
        });

        $(document).on('click', '#updateSbuFloorBtn', function(e) {
            e.preventDefault();
            updateFloor();
        });

        $(document).on('click', '.delete-floor-btn', function(e) {
            e.preventDefault();
            deleteFloor(this);
        });

        const addFloorForm = document.getElementById('addSbuFloorForm');
        if (addFloorForm) {
            addFloorForm.querySelectorAll('input, select, textarea').forEach(function(el) {
                el.addEventListener('change', syncFloorLimitedFieldsState);
                el.addEventListener('input', syncFloorLimitedFieldsState);
            });
        }

        const editFloorForm = document.getElementById('editSbuFloorForm');
        if (editFloorForm) {
            editFloorForm.querySelectorAll('input, select, textarea').forEach(function(el) {
                el.addEventListener('change', syncEditFloorLimitedFieldsState);
                el.addEventListener('input', syncEditFloorLimitedFieldsState);
            });
        }

        setSbuOptions('#sbu_id', $('#organization_id').val(), 'First select organization');
        setSbuOptions('#edit_sbu_id', $('#edit_organization_id').val(), 'First select organization');
        refreshAddFloorBiometricList();
        refreshEditFloorBiometricList([]);
        syncFloorLimitedFieldsState();
        syncEditFloorLimitedFieldsState();
    }
})();