/**
 * Organization Module
 * Manage SBU (Strategic Business Units) and organizations
 */

(function() {
    'use strict';

    $(document).ready(function() {
        initializeEventHandlers();
    });

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
    }
})();

