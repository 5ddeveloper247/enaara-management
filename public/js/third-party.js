(function() {
    'use strict';

    $(document).ready(function() {
        initializeEventHandlers();
    });

    function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
    }

    function applyTpFilters() {
        const status = $('input[name="filterTpStatus"]:checked').val();

        $('#thirdPartiesGrid .col-md-6').each(function() {
            const card = $(this).find('.third-party-card');
            const cardStatus = String(card.data('tp-status'));

            if (status === 'all' || cardStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function clearTpFilters() {
        $('input#filterTpStatusAll').prop('checked', true);
        $('#thirdPartiesGrid .col-md-6').show();
    }

    function populateTpDetailCanvas(button) {
        const get = function(attr, fallback) {
            const v = button.getAttribute(attr);
            return (v !== null && v !== '') ? v : (fallback || '—');
        };

        const name = get('data-tp-name');
        const thirdPartyName = get('data-tp-third-party-name');

        $('#detailTpLogoPlaceholder').text((name.substring(0, 2) || '?').toUpperCase());
        $('#detailTpName').text(name);
        $('#detailTpThirdPartyName').text(thirdPartyName);
        $('#detailTpCity').text(get('data-tp-city'));
        $('#detailTpOrganization').text(get('data-organization-name'));
        $('#detailTpAddress').text(get('data-tp-address'));

        const lat = button.getAttribute('data-tp-latitude');
        const lng = button.getAttribute('data-tp-longitude');

        $('#detailTpCoordinates').text((lat && lng) ? lat + ', ' + lng : '—');
        $('#detailTpStatus').text(get('data-tp-active') === '1' ? 'Active' : 'Inactive');
    }

    function clearFormMessages(formSelector) {
        $(formSelector + ' .is-invalid').removeClass('is-invalid');
        $(formSelector + ' .invalid-feedback').remove();
        $(formSelector + ' .form-alert-box').remove();
    }

    function showFormMessage(formSelector, message, type) {
        type = type || 'danger';
        $(formSelector + ' .form-alert-box').remove();

        const html = `
            <div class="alert alert-${type} form-alert-box mt-2 mb-3" role="alert">
                ${message}
            </div>
        `;

        $(formSelector).prepend(html);
    }

    function resetAddTpForm() {
        const form = document.getElementById('addThirdPartyForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#addThirdPartyForm');
        $('#is_active').val('1');
    }

    function resetEditTpForm() {
        const form = document.getElementById('editThirdPartyForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#editThirdPartyForm');
        $('#edit_is_active').val('1');
        $('#editThirdPartyForm').attr('data-update-url', '');
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

    function storeThirdParty() {
        const form = $('#addThirdPartyForm');
        const url = form.data('store-url');

        clearFormMessages('#addThirdPartyForm');

        if (!url) {
            showFormMessage('#addThirdPartyForm', 'Store URL not found.');
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
                $('#saveThirdPartyBtn')
                    .prop('disabled', true)
                    .html('Saving...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('addThirdPartyCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Third party created successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    showFormMessage('#addThirdPartyForm', response.message || 'Failed to create.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#addThirdPartyForm', xhr.responseJSON.errors);
                } else {
                    showFormMessage(
                        '#addThirdPartyForm',
                        (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Failed to create third party.'
                    );
                }
            },
            complete: function() {
                $('#saveThirdPartyBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Create Third Party');
            }
        });
    }

    function loadEditTpData(button) {
        const editUrl = $(button).data('edit-url');

        clearFormMessages('#editThirdPartyForm');

        if (!editUrl) {
            showFormMessage('#editThirdPartyForm', 'Edit URL not found.');
            return;
        }

        resetEditTpForm();

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
                    $('#edit_third_party_name').val(data.third_party_name ?? '');
                    $('#edit_city').val(data.city ?? '');
                    $('#edit_address').val(data.address ?? '');
                    $('#edit_latitude').val(data.latitude ?? '');
                    $('#edit_longitude').val(data.longitude ?? '');
                    $('#edit_is_active').val(
                        data.is_active === 1 || data.is_active === '1' || data.is_active === true ? '1' : '0'
                    );

                    $('#editThirdPartyForm').attr('data-update-url', $(button).data('update-url'));
                } else {
                    showFormMessage('#editThirdPartyForm', response.message || 'Failed to load data.');
                }
            },
            error: function(xhr) {
                showFormMessage(
                    '#editThirdPartyForm',
                    (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Failed to fetch third party.'
                );
            }
        });
    }

    function updateThirdParty() {
        const form = $('#editThirdPartyForm');
        const url = form.attr('data-update-url');

        clearFormMessages('#editThirdPartyForm');

        if (!url) {
            showFormMessage('#editThirdPartyForm', 'Update URL not found.');
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
                $('#updateThirdPartyBtn')
                    .prop('disabled', true)
                    .html('Updating...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('editThirdPartyCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Third party updated successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    showFormMessage('#editThirdPartyForm', response.message || 'Failed to update.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#editThirdPartyForm', xhr.responseJSON.errors);
                } else {
                    showFormMessage(
                        '#editThirdPartyForm',
                        (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Failed to update third party.'
                    );
                }
            },
            complete: function() {
                $('#updateThirdPartyBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Update Third Party');
            }
        });
    }

    function deleteThirdParty(button) {
        const deleteUrl = $(button).data('delete-url');

        if (!deleteUrl) {
            showFormMessage('#editThirdPartyForm', 'Delete URL not found.');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'This third party will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(function(result) {
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
                            text: response.message || 'Deleted successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Failed.' });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete.'
                    });
                }
            });
        });
    }

    function initializeEventHandlers() {
        const detailCanvas = document.getElementById('thirdPartyDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('view-tp-btn')) {
                    populateTpDetailCanvas(button);
                }
            });
        }

        const addCanvas = document.getElementById('addThirdPartyCanvas');
        if (addCanvas) {
            addCanvas.addEventListener('show.bs.offcanvas', function() {
                resetAddTpForm();
            });
        }

        const editCanvas = document.getElementById('editThirdPartyCanvas');
        if (editCanvas) {
            editCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('edit-tp-btn')) {
                    loadEditTpData(button);
                }
            });

            editCanvas.addEventListener('hidden.bs.offcanvas', function() {
                resetEditTpForm();
            });
        }

        $('.filter-tp-status').on('change', function() {
            applyTpFilters();
        });

        $('#clearTpFiltersBtn').on('click', function() {
            clearTpFilters();
        });

        $(document).on('click', '#saveThirdPartyBtn', function(e) {
            e.preventDefault();
            storeThirdParty();
        });

        $(document).on('click', '#updateThirdPartyBtn', function(e) {
            e.preventDefault();
            updateThirdParty();
        });

        $(document).on('click', '.delete-tp-btn', function(e) {
            e.preventDefault();
            deleteThirdParty(this);
        });
    }
})();
