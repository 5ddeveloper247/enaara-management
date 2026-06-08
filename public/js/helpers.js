/**
 * Common Helper Functions
 * Shared utility functions for the entire project
 */

/**
 * Initialize DataTable with custom options
 */
function initUserDataTable(tableId, options = {}) {
    const defaultOptions = {
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[0, 'asc']],
        scrollX: false,
        language: {
            search: "",
            searchPlaceholder: "Search users...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No matching records found"
        },
        dom: '<"row px-4 py-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end gap-2"<B><f>>>rt<"row px-4 py-2"<"col-md-5"i><"col-md-7"p>>',
        ...options
    };

    const mergedOptions = $.extend(true, {}, defaultOptions, options);
    const table = $(tableId).DataTable(mergedOptions);

    // Auto adjust column widths after init
    setTimeout(() => {
        table.columns.adjust().draw();
    }, 100);

    // Auto adjust on window resize
    $(window).on('resize', function() {
        table.columns.adjust();
    });

    return table;
}

/**
 * Get hidden columns for a specific row
 */
function getHiddenColumnsForRow(table, rowNode) {
    const hiddenColumns = [];
    const $row = $(rowNode);

    table.columns().every(function(index) {
        const column = table.column(index);
        const $header = $(column.header());

        if (!column.visible() && !$header.hasClass('no-toggle')) {
            const $cell = $row.find(`td:eq(${index})`);
            hiddenColumns.push({
                index: index,
                title: $header.text().trim(),
                content: $cell.html() || 'N/A'
            });
        }
    });

    return hiddenColumns;
}

/**
 * Extract a user-facing error message from a jQuery AJAX error response.
 */
function getAjaxErrorMessage(xhr, fallback = 'Something went wrong. Please try again.') {
    const data = xhr && xhr.responseJSON ? xhr.responseJSON : null;

    if (!data) {
        if (xhr && xhr.status === 419) {
            return 'Your session has expired. Please refresh the page and try again.';
        }
        if (xhr && xhr.status === 403) {
            return 'You are not authorized to perform this action.';
        }
        return fallback;
    }

    if (data.errors && typeof data.errors === 'object') {
        const messages = Object.values(data.errors).flat().filter(Boolean);
        if (messages.length > 0) {
            return messages.join('\n');
        }
    }

    if (data.message) {
        return data.message;
    }

    return fallback;
}


