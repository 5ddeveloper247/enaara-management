(function() {
    'use strict';

    $(document).ready(function() {
        initializeEventHandlers();
    });

    function applyFilters() {
        const status = $('input[name="filterStatus"]:checked').val();
        $('#sbuFloorsGrid .col-md-6').each(function() {
            const card = $(this).find('.sbu-floor-card');
            const cardStatus = card.data('floor-status');
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

    function ucfirst(str) {
        if (!str) return '—';
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
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
        $('.filter-status').on('change', function() {
            applyFilters();
        });
        $('#clearFiltersBtn').on('click', function() {
            clearFilters();
        });
    }
})();
