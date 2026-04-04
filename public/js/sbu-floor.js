(function() {
    'use strict';

    $(document).ready(function() {
        initializeEventHandlers();
    });

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
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

    function populateDetailCanvas(button) {
        const get = function(attr, fallback) {
            const v = button.getAttribute(attr);
            return (v !== null && v !== '') ? v : (fallback || '—');
        };

        const name = get('data-floor-name');

        $('#detailFloorLogoPlaceholder').text((name.substring(0, 1) || 'F').toUpperCase());
        $('#detailFloorName').text(name);
        $('#detailFloorType').text(ucfirst(get('data-floor-type')));
        $('#detailFloorSbuName').text(get('data-sbu-name'));
        $('#detailFloorNumber').text(get('data-floor-number'));
        $('#detailFloorRestricted').text(get('data-floor-restricted') === '1' ? 'Yes' : 'No');
        $('#detailFloorStatus').text(get('data-floor-active') === '1' ? 'Active' : 'Inactive');
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

    function resetAddFloorForm() {
        const form = document.getElementById('addSbuFloorForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#addSbuFloorForm');
        $('#floor_type').val('operational');
        $('#is_restricted').val('0');
        $('#is_active').val('1');
    }

    function resetEditFloorForm() {
        const form = document.getElementById('editSbuFloorForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#editSbuFloorForm');
        $('#edit_floor_type').val('operational');
        $('#edit_is_restricted').val('0');
        $('#edit_is_active').val('1');
        $('#editSbuFloorForm').attr('data-update-url', '');
        $('#deleteSbuFloorBtn').attr('data-delete-url', '');
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
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to create SBU floor.'
                    });
                }
            },
            complete: function() {
                $('#saveSbuFloorBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Create Floor');
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
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update SBU floor.'
                    });
                }
            },
            complete: function() {
                $('#updateSbuFloorBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Update Floor');
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
                const button = event.relatedTarget;

                if (button && button.classList.contains('view-floor-btn')) {
                    populateDetailCanvas(button);
                }
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
    }
})();