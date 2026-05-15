(function() {
    'use strict';

    const organizations = Array.isArray(window.designationOrganizations) ? window.designationOrganizations : [];

    $(document).ready(function() {
        initializeEventHandlers();
    });

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function findOrganization(orgId) {
        return organizations.find(function(org) {
            return String(org.id) === String(orgId);
        }) || null;
    }

    function setSelectOptions(select, placeholder, items, selectedValue) {
        const $select = $(select);
        $select.empty();

        if (placeholder) {
            $select.append(`<option value="" hidden>${placeholder}</option>`);
        }

        (items || []).forEach(function(item) {
            const isSelected = selectedValue !== null && selectedValue !== undefined && String(item.id) === String(selectedValue);
            $select.append(`<option value="${item.id}"${isSelected ? ' selected' : ''}>${item.name}</option>`);
        });
    }

    function resetFormCascade(orgSelector, sbuSelector) {
        $(orgSelector).val('');
        setSelectOptions(sbuSelector, '— Select SBU —', []);
        $(sbuSelector).prop('disabled', true);
    }

    function populateFormSbus(orgSelector, sbuSelector, selectedSbuId) {
        const orgId = $(orgSelector).val();
        const organization = findOrganization(orgId);

        setSelectOptions(sbuSelector, '— Select SBU —', organization ? organization.sbus : [], selectedSbuId);
        $(sbuSelector).prop('disabled', !organization);
    }

    function setFormCascade(orgSelector, sbuSelector, organizationId, sbuId) {
        $(orgSelector).val(organizationId || '');
        populateFormSbus(orgSelector, sbuSelector, sbuId);

        if (sbuId) {
            $(sbuSelector).val(sbuId);
        }
    }

    function applyFilters() {
        const status = $('input[name="filterStatus"]:checked').val();

        $('#designationsGrid .col-md-6').each(function() {
            const card = $(this).find('.sbu-card');
            const cardStatus = String(card.data('designation-status'));

            if (status === 'all' || cardStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function clearFilters() {
        $('input#filterStatusAll').prop('checked', true);
        $('#designationsGrid .col-md-6').show();
    }

    function populateDetailCanvas(button) {
        const get = function(attr, fallback) {
            const v = button.getAttribute(attr);
            return (v !== null && v !== '') ? v : (fallback || '—');
        };

        const name = get('data-designation-name');
        const organization = get('data-designation-organization');
        const sbu = get('data-designation-sbu');

        $('#detailDesignationLogoPlaceholder').text((name.substring(0, 2) || '?').toUpperCase());
        $('#detailDesignationName').text(name);
        $('#detailDesignationScopeBadge').text([organization, sbu].filter(function(value) {
            return value && value !== '—';
        }).join(' · ') || '—');
        $('#detailDesignationOrganization').text(organization);
        $('#detailDesignationSbu').text(sbu);
        $('#detailDesignationDescription').text(get('data-designation-description'));
        $('#detailDesignationStatus').text(get('data-designation-active') === '1' ? 'Active' : 'Inactive');
    }

    function clearFormMessages(formSelector) {
        $(formSelector + ' .is-invalid').removeClass('is-invalid');
        $(formSelector + ' .validation-error-dynamic').remove();
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

    function resetAddForm() {
        const form = document.getElementById('addDesignationForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#addDesignationForm');
        $('#ds_is_active').val('1');
        resetFormCascade('#ds_organization_id', '#ds_sbu_id');
    }

    function resetEditForm() {
        const form = document.getElementById('editDesignationForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#editDesignationForm');
        $('#edit_ds_is_active').val('1');
        $('#editDesignationForm').attr('data-update-url', '');
        resetFormCascade('#edit_ds_organization_id', '#edit_ds_sbu_id');
    }

    function showValidationErrors(formSelector, errors) {
        clearFormMessages(formSelector);
        const fieldMap = formSelector === '#addDesignationForm'
            ? {
                organization_id: '#ds_organization_id',
                sbu_id: '#ds_sbu_id',
                name: '#ds_name',
                description: '#ds_description',
                is_active: '#ds_is_active',
            }
            : {
                organization_id: '#edit_ds_organization_id',
                sbu_id: '#edit_ds_sbu_id',
                name: '#edit_ds_name',
                description: '#edit_ds_description',
                is_active: '#edit_ds_is_active',
            };

        $.each(errors || {}, function(field, messages) {
            const normalizedField = String(field).replace(/\.\d+$/, '');
            const message = Array.isArray(messages) ? messages[0] : messages;
            const inputSelector = fieldMap[normalizedField];
            if (!inputSelector || !message) {
                return;
            }
            const input = $(inputSelector);
            if (!input.length) {
                return;
            }
            input.addClass('is-invalid');
            if ($(formSelector + ` [data-error-for="${normalizedField}"]`).length === 0) {
                input.after(`<div class="invalid-feedback d-block validation-error-dynamic" data-error-for="${normalizedField}">${message}</div>`);
            }
        });

        const firstInvalid = $(formSelector + ' .is-invalid').first();
        if (firstInvalid.length) {
            firstInvalid.trigger('focus');
        }
    }

    function storeDesignation() {
        const form = $('#addDesignationForm');
        const url = form.data('store-url');

        clearFormMessages('#addDesignationForm');

        if (!url) {
            showFormMessage('#addDesignationForm', 'Store URL not found.');
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
                $('#saveDesignationBtn')
                    .prop('disabled', true)
                    .html('Saving...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('addDesignationCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Designation created successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showFormMessage('#addDesignationForm', response.message || 'Failed to create designation.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#addDesignationForm', xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to create designation.'
                    });
                }
            },
            complete: function() {
                $('#saveDesignationBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Create Designation');
            }
        });
    }

    function loadEditData(button) {
        const editUrl = $(button).data('edit-url');

        clearFormMessages('#editDesignationForm');

        if (!editUrl) {
            showFormMessage('#editDesignationForm', 'Edit URL not found.');
            return;
        }

        resetEditForm();

        $.ajax({
            url: editUrl,
            type: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success && response.data) {
                    const data = response.data;

                    $('#edit_ds_id').val(data.id ?? '');
                    setFormCascade(
                        '#edit_ds_organization_id',
                        '#edit_ds_sbu_id',
                        data.organization_id ?? '',
                        data.sbu_id ?? ''
                    );
                    $('#edit_ds_name').val(data.name ?? '');
                    $('#edit_ds_description').val(data.description ?? '');
                    $('#edit_ds_is_active').val(
                        data.is_active === 1 || data.is_active === '1' || data.is_active === true ? '1' : '0'
                    );

                    $('#editDesignationForm').attr('data-update-url', $(button).data('update-url'));
                } else {
                    showFormMessage('#editDesignationForm', response.message || 'Failed to load designation data.');
                }
            },
            error: function(xhr) {
                showFormMessage(
                    '#editDesignationForm',
                    (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Failed to fetch designation.'
                );
            }
        });
    }

    function updateDesignation() {
        const form = $('#editDesignationForm');
        const url = form.attr('data-update-url');

        clearFormMessages('#editDesignationForm');

        if (!url) {
            showFormMessage('#editDesignationForm', 'Update URL not found.');
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
                $('#updateDesignationBtn')
                    .prop('disabled', true)
                    .html('Updating...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('editDesignationCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Designation updated successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showFormMessage('#editDesignationForm', response.message || 'Failed to update designation.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#editDesignationForm', xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update designation.'
                    });
                }
            },
            complete: function() {
                $('#updateDesignationBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Update Designation');
            }
        });
    }

    function deleteDesignation(button) {
        const deleteUrl = $(button).data('delete-url');

        if (!deleteUrl) {
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'This designation will be permanently deleted.',
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
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: response.message || 'Designation deleted successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to delete designation.'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete designation.'
                    });
                }
            });
        });
    }

    function initializeEventHandlers() {
        const detailCanvas = document.getElementById('designationDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('view-designation-btn')) {
                    populateDetailCanvas(button);
                }
            });
        }

        const addCanvas = document.getElementById('addDesignationCanvas');
        if (addCanvas) {
            addCanvas.addEventListener('show.bs.offcanvas', function() {
                resetAddForm();
            });
        }

        const editCanvas = document.getElementById('editDesignationCanvas');
        if (editCanvas) {
            editCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('edit-designation-btn')) {
                    loadEditData(button);
                }
            });

            editCanvas.addEventListener('hidden.bs.offcanvas', function() {
                resetEditForm();
            });
        }

        $('.filter-status').on('change', function() {
            applyFilters();
        });

        $('#ds_organization_id').on('change', function() {
            populateFormSbus('#ds_organization_id', '#ds_sbu_id');
        });

        $('#edit_ds_organization_id').on('change', function() {
            populateFormSbus('#edit_ds_organization_id', '#edit_ds_sbu_id');
        });

        $('#clearFiltersBtn').on('click', function() {
            clearFilters();
        });

        $(document).on('click', '#saveDesignationBtn', function(e) {
            e.preventDefault();
            storeDesignation();
        });

        $(document).on('click', '#updateDesignationBtn', function(e) {
            e.preventDefault();
            updateDesignation();
        });

        $(document).on('click', '.delete-designation-btn', function(e) {
            e.preventDefault();
            deleteDesignation(this);
        });
    }
})();
