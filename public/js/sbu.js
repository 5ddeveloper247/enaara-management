(function() {
    'use strict';

    $(document).ready(function() {
        initializeEventHandlers();
    });

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
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
        $(formSelector + ' .invalid-feedback').remove();
        $(formSelector + ' .form-alert-box').remove();
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
        $('#sbuScheduleModeStandard').prop('checked', true);
        toggleAddSbuScheduleMode();
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
        $('#editSbuScheduleModeStandard').prop('checked', true);
        toggleEditSbuScheduleMode();
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
                openingGracePeriod: '',
                closingGracePeriod: ''
            };
        }
        return {
            workingDays: (option.dataset.workingDays || '').split(',').filter(Boolean),
            workingStartTime: option.dataset.workingStartTime || '',
            workingEndTime: option.dataset.workingEndTime || '',
            openingGracePeriod: option.dataset.openingGracePeriod || '',
            closingGracePeriod: option.dataset.closingGracePeriod || ''
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
        $('#sbuOpeningGracePeriod').val(schedule.openingGracePeriod);
        $('#sbuClosingGracePeriod').val(schedule.closingGracePeriod);
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
        $('#editSbuOpeningGracePeriod').val(schedule.openingGracePeriod);
        $('#editSbuClosingGracePeriod').val(schedule.closingGracePeriod);
    }

    function schedulesMatchParentForEdit(currentWorkingDays, currentStartTime, currentEndTime, currentOpeningGracePeriod, currentClosingGracePeriod) {
        const option = getEditSelectedOrganizationOption();
        const schedule = getScheduleFromOption(option);
        const current = [...currentWorkingDays].sort().join(',');
        const parent = [...schedule.workingDays].sort().join(',');
        return current === parent
            && (currentStartTime || '') === schedule.workingStartTime
            && (currentEndTime || '') === schedule.workingEndTime
            && (currentOpeningGracePeriod || '') === schedule.openingGracePeriod
            && (currentClosingGracePeriod || '') === schedule.closingGracePeriod;
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
            const input = $(formSelector + ' [name="' + field + '"]');

            if (input.length) {
                input.addClass('is-invalid');
                input.after('<div class="invalid-feedback d-block">' + messages[0] + '</div>');
            }
        });
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
                    .html('Saving...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('addSbuCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'SBU created successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showFormMessage('#addSbuForm', response.message || 'Failed to create SBU.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let errorMessage = '<div class="text-start mt-2"><ul class="mb-0">';
                    Object.values(xhr.responseJSON.errors).flat().forEach(err => {
                        errorMessage += `<li>${err}</li>`;
                    });
                    errorMessage += '</ul></div>';

                    Swal.fire({
                        icon: 'warning',
                        title: 'Please check the following:',
                        html: errorMessage,
                        confirmButtonColor: '#1a237e',
                        confirmButtonText: 'Understood'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to create SBU.'
                    });
                }
            },
            complete: function() {
                $('#saveSbuBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Create SBU');
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
                    const editOpeningGracePeriod = (data.opening_grace_period ?? '').toString();
                    const editClosingGracePeriod = (data.closing_grace_period ?? '').toString();
                    $('#editSbuOpeningGracePeriod').val(editOpeningGracePeriod);
                    $('#editSbuClosingGracePeriod').val(editClosingGracePeriod);
                    $('#edit_is_active').val(
                        data.is_active === 1 || data.is_active === '1' || data.is_active === true ? '1' : '0'
                    );
                    if ((data.organization_id ?? '') !== '') {
                        if (schedulesMatchParentForEdit(editWorkingDays, editStartTime, editEndTime, editOpeningGracePeriod, editClosingGracePeriod)) {
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
                    .html('Updating...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('editSbuCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'SBU updated successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showFormMessage('#editSbuForm', response.message || 'Failed to update SBU.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let errorMessage = '<div class="text-start mt-2"><ul class="mb-0">';
                    Object.values(xhr.responseJSON.errors).flat().forEach(err => {
                        errorMessage += `<li>${err}</li>`;
                    });
                    errorMessage += '</ul></div>';

                    Swal.fire({
                        icon: 'warning',
                        title: 'Please check the following:',
                        html: errorMessage,
                        confirmButtonColor: '#1a237e',
                        confirmButtonText: 'Understood'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update SBU.'
                    });
                }
            },
            complete: function() {
                $('#updateSbuBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Update SBU');
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

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: response.message || 'SBU deleted successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        showFormMessage('#editSbuForm', response.message || 'Failed to delete SBU.');
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete SBU.'
                    });
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
            if (this.value) {
                $('#sbuScheduleModeStandard').prop('checked', true);
            }
            toggleAddSbuScheduleMode();
        });
        $('#sbuScheduleModeStandard, #sbuScheduleModeCustom').on('change', function() {
            toggleAddSbuScheduleMode();
        });

        $(document).on('click', '#updateSbuBtn', function(e) {
            e.preventDefault();
            updateSbu();
        });

        $('#edit_organization_id').on('change', function() {
            if (this.value) {
                $('#editSbuScheduleModeStandard').prop('checked', true);
            } else {
                $('#editSbuScheduleModeCustom').prop('checked', true);
            }
            toggleEditSbuScheduleMode();
        });
        $('#editSbuScheduleModeStandard, #editSbuScheduleModeCustom').on('change', function() {
            toggleEditSbuScheduleMode();
        });

        $(document).on('click', '.delete-sbu-btn', function(e) {
            e.preventDefault();
            deleteSbu(this);
        });

        toggleAddSbuScheduleMode();
        toggleEditSbuScheduleMode();
    }
})();