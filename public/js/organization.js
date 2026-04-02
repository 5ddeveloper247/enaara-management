/**
 * Organization Module
 * Manage SBU (Strategic Business Units) and organizations
 */

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
        $('#organizationsGrid .col-md-6').each(function() {
            const card = $(this).find('.organization-card');
            const cardStatus = card.data('org-status');
            if (status === 'all' || cardStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function clearFilters() {
        $('input#filterStatusAll').prop('checked', true);
        $('#organizationsGrid .col-md-6').show();
    }

    function populateDetailCanvas(button) {
        const get = function(attr, fallback) {
            const v = button.getAttribute(attr);
            return (v !== null && v !== '') ? v : (fallback || '—');
        };
        $('#detailOrgName').text(get('data-org-name'));
        $('#detailOrgRegNumber').text('Code: ' + get('data-org-code'));
        $('#detailOrgLogoPlaceholder').text((get('data-org-name').substring(0, 2) || '?').toUpperCase()).show();
        $('#detailOrgLogo').hide();
        $('#detailOrgAddress').text(get('data-org-address'));
        $('#detailOrgWebsite').html('<span class="text-muted">' + (get('data-org-email') !== '—' ? get('data-org-email') : 'Not provided') + '</span>');
        $('#detailOrgHeadcount').text('—');
        $('#detailOrgDepartments').text('—');
        $('#editOrganizationBtn').attr('data-org-id', get('data-org-id'));
    }

    function deleteOrganization(button) {
        const deleteUrl = $(button).data('delete-url');

        if (!deleteUrl) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Delete URL not found.'
            });
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'This organization will be permanently deleted!',
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
                    $(button).prop('disabled', true).html('<i class="bi bi-trash"></i>...');
                },
                success: function(response) {
                    if (response.success) {
                        const canvasEl = document.getElementById('organizationEditCanvas');
                        if (canvasEl) {
                            const offcanvas = bootstrap.Offcanvas.getInstance(canvasEl);
                            if (offcanvas) offcanvas.hide();
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: response.message || 'Organization deleted successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to delete organization.'
                        });
                    }
                },
                error: function(xhr) {
                    let msg = 'Failed to delete organization.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msg
                    });
                },
                complete: function() {
                    $(button).prop('disabled', false).html('<i class="bi bi-trash"></i>');
                }
            });
        });
    }

    function initializeEventHandlers() {
        const organizationDetailCanvas = document.getElementById('organizationDetailCanvas');
        if (organizationDetailCanvas) {
            organizationDetailCanvas.addEventListener('show.bs.offcanvas', function(event) {
                const button = event.relatedTarget;
                if (button && button.classList.contains('view-organization-btn')) {
                    populateDetailCanvas(button);
                }
            });
        }

        $('.filter-status').on('change', function() {
            applyFilters();
        });

        $('#clearFiltersBtn').on('click', function() {
            clearFilters();
        });

        $('#editOrganizationBtn').on('click', function() {
            const orgId = $(this).attr('data-org-id');
        });

        $(document).on('click', '.delete-organization-btn', function(e) {
            e.preventDefault();
            deleteOrganization(this);
        });
    }
})();

