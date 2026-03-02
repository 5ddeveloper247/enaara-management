(function() {
    'use strict';

    $(document).ready(function() {
        initializeEventHandlers();
    });

    function applyFilters() {
        const status = $('input[name="filterStatus"]:checked').val();
        $('#sbusGrid .col-md-6').each(function() {
            const card = $(this).find('.sbu-card');
            const cardStatus = card.data('sbu-status');
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
        $('.filter-status').on('change', function() {
            applyFilters();
        });
        $('#clearFiltersBtn').on('click', function() {
            clearFilters();
        });
    }
})();
