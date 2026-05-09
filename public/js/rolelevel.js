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

        $('#roleLevelsGrid .col-md-6').each(function() {
            const card = $(this).find('.sbu-card');
            const cardStatus = String(card.data('rolelevel-status'));

            if (status === 'all' || cardStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function clearFilters() {
        $('input#filterStatusAll').prop('checked', true);
        $('#roleLevelsGrid .col-md-6').show();
    }

    function populateDetailCanvas(button) {
        const get = function(attr, fallback) {
            const v = button.getAttribute(attr);
            return (v !== null && v !== '') ? v : (fallback || '—');
        };

        const name = get('data-rolelevel-name');

        $('#detailRoleLevelLogoPlaceholder').text((name.substring(0, 2) || '?').toUpperCase());
        $('#detailRoleLevelName').text(name);
        $('#detailRoleLevelLevelBadge').text('Level ' + get('data-rolelevel-level'));
        $('#detailRoleLevelLevel').text(get('data-rolelevel-level'));
        $('#detailRoleLevelGrade').text(get('data-rolelevel-grade'));
        $('#detailRoleLevelDescription').text(get('data-rolelevel-description'));
        $('#detailRoleLevelStatus').text(get('data-rolelevel-active') === '1' ? 'Active' : 'Inactive');
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
        const form = document.getElementById('addRoleLevelForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#addRoleLevelForm');
        $('#rl_is_active').val('1');
    }

    function resetEditForm() {
        const form = document.getElementById('editRoleLevelForm');

        if (form) {
            form.reset();
        }

        clearFormMessages('#editRoleLevelForm');
        $('#edit_rl_is_active').val('1');
        $('#editRoleLevelForm').attr('data-update-url', '');
    }

    function enforceLevelInputLimit(inputSelector) {
        const input = document.querySelector(inputSelector);
        if (!input) {
            return;
        }

        input.addEventListener('input', function() {
            let value = String(this.value || '').replace(/\D/g, '');
            if (value.length > 10) {
                value = value.slice(0, 10);
                this.setCustomValidity('You can enter maximum 10 digits for role level priority.');
            } else {
                this.setCustomValidity('');
            }
            this.value = value;
        });
    }

    function showValidationErrors(formSelector, errors) {
        clearFormMessages(formSelector);
        const fieldMap = formSelector === '#addRoleLevelForm'
            ? {
                name: '#rl_name',
                level: '#rl_level',
                grade: '#rl_grade',
                description: '#rl_description',
                is_active: '#rl_is_active',
            }
            : {
                name: '#edit_rl_name',
                level: '#edit_rl_level',
                grade: '#edit_rl_grade',
                description: '#edit_rl_description',
                is_active: '#edit_rl_is_active',
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

    function storeRoleLevel() {
        const form = $('#addRoleLevelForm');
        const url = form.data('store-url');

        clearFormMessages('#addRoleLevelForm');

        if (!url) {
            showFormMessage('#addRoleLevelForm', 'Store URL not found.');
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
                $('#saveRoleLevelBtn')
                    .prop('disabled', true)
                    .html('Saving...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('addRoleLevelCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Role Level created successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showFormMessage('#addRoleLevelForm', response.message || 'Failed to create Role Level.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#addRoleLevelForm', xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to create Role Level.'
                    });
                }
            },
            complete: function() {
                $('#saveRoleLevelBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Create Role Level');
            }
        });
    }

    function loadEditData(button) {
        const editUrl = $(button).data('edit-url');

        clearFormMessages('#editRoleLevelForm');

        if (!editUrl) {
            showFormMessage('#editRoleLevelForm', 'Edit URL not found.');
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

                    $('#edit_rl_id').val(data.id ?? '');
                    $('#edit_rl_name').val(data.name ?? '');
                    $('#edit_rl_level').val(data.level ?? '');
                    $('#edit_rl_grade').val(data.grade ?? '');
                    $('#edit_rl_description').val(data.description ?? '');
                    $('#edit_rl_is_active').val(
                        data.is_active === 1 || data.is_active === '1' || data.is_active === true ? '1' : '0'
                    );

                    $('#editRoleLevelForm').attr('data-update-url', $(button).data('update-url'));
                } else {
                    showFormMessage('#editRoleLevelForm', response.message || 'Failed to load Role Level data.');
                }
            },
            error: function(xhr) {
                showFormMessage(
                    '#editRoleLevelForm',
                    (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Failed to fetch Role Level.'
                );
            }
        });
    }

    function updateRoleLevel() {
        const form = $('#editRoleLevelForm');
        const url = form.attr('data-update-url');

        clearFormMessages('#editRoleLevelForm');

        if (!url) {
            showFormMessage('#editRoleLevelForm', 'Update URL not found.');
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
                $('#updateRoleLevelBtn')
                    .prop('disabled', true)
                    .html('Updating...');
            },
            success: function(response) {
                if (response.success) {
                    const canvasEl = document.getElementById('editRoleLevelCanvas');
                    const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);

                    if (offcanvas) {
                        offcanvas.hide();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Role Level updated successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    showFormMessage('#editRoleLevelForm', response.message || 'Failed to update Role Level.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showValidationErrors('#editRoleLevelForm', xhr.responseJSON.errors);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to update Role Level.'
                    });
                }
            },
            complete: function() {
                $('#updateRoleLevelBtn')
                    .prop('disabled', false)
                    .html('<i class="bi bi-check-lg me-1"></i>Update Role Level');
            }
        });
    }

    function deleteRoleLevel(button) {
        const deleteUrl = $(button).data('delete-url');

        if (!deleteUrl) {
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'This Role Level will be permanently deleted.',
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
                            text: response.message || 'Role Level deleted successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to delete Role Level.'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'System Error',
                        text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to delete Role Level.'
                    });
                }
            });
        });
    }

    function initializeEventHandlers() {
        enforceLevelInputLimit('#rl_level');
        enforceLevelInputLimit('#edit_rl_level');

        const detailCanvas = document.getElementById('roleLevelDetailCanvas');
        if (detailCanvas) {
            detailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('view-rolelevel-btn')) {
                    populateDetailCanvas(button);
                }
            });
        }

        const addCanvas = document.getElementById('addRoleLevelCanvas');
        if (addCanvas) {
            addCanvas.addEventListener('show.bs.offcanvas', function() {
                resetAddForm();
            });
        }

        const editCanvas = document.getElementById('editRoleLevelCanvas');
        if (editCanvas) {
            editCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;

                if (button && button.classList.contains('edit-rolelevel-btn')) {
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

        $('#clearFiltersBtn').on('click', function() {
            clearFilters();
        });

        $(document).on('click', '#saveRoleLevelBtn', function(e) {
            e.preventDefault();
            storeRoleLevel();
        });

        $(document).on('click', '#updateRoleLevelBtn', function(e) {
            e.preventDefault();
            updateRoleLevel();
        });

        $(document).on('click', '.delete-rolelevel-btn', function(e) {
            e.preventDefault();
            deleteRoleLevel(this);
        });
    }
})();
