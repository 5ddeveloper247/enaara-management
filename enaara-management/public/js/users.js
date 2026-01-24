// let rowToDelete;
// let table;

// $(document).ready(function () {
//     table = initDataTable('#userTable', {
//         columnDefs: [{
//             targets: [0, 1, 5, 8, 13, 14], // visible by default
//             visible: true,
//             className: 'default-col'
//         },
//         {
//             targets: [2, 3, 4, 6, 7, 9, 10, 11, 12], // hidden initially
//             visible: false
//         }
//         ],
//         buttons: [{
//             extend: 'colvis',
//             text: 'Select Columns',
//             columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]
//         }]
//     });



//     // When delete button clicked
//     $('#userTable').on('click', '.delete-btn', function () {
//         rowToDelete = $(this).closest('tr');
//         $('#deleteConfirmModal').modal('show');
//     });



//     // Confirm delete
//     $('#confirmDeleteBtn').on('click', function () {
//         if (rowToDelete) {
//             if ($.fn.DataTable.isDataTable('#userTable')) {
//                 table.row(rowToDelete).remove().draw();
//             } else {
//                 rowToDelete.remove();
//             }
//         }

//         // Close first modal
//         $('#deleteConfirmModal').modal('hide');


//         $('#deleteConfirmModal').on('hidden.bs.modal', function () {
//             $('#secondaryAlertModal').modal('show');
//             $(this).off(
//                 'hidden.bs.modal');
//         });
//     });



//     let customerFilters = [{
//         colIndex: 0,
//         selector: '#filterCustomerId',
//         type: 'text'
//     },
//     {
//         colIndex: 1,
//         selector: '#filterName',
//         type: 'text'
//     },
//     {
//         colIndex: 2,
//         selector: '#filterCountry',
//         type: 'multi',
//         regex: true
//     },
//     {
//         colIndex: 3,
//         selector: '#filterShippingMark',
//         type: 'text'
//     },
//     {
//         colIndex: 4,
//         selector: '#filterWebsiteUser',
//         type: 'text'
//     },
//     {
//         colIndex: 5,
//         selector: '#filterPhone',
//         type: 'text'
//     },
//     {
//         colIndex: 6,
//         selector: '#filterEmail',
//         type: 'text'
//     },
//     {
//         colIndex: 8,
//         selector: '#filterHasImage',
//         type: 'checkbox',
//         valueIfChecked: 'Yes'
//     },
//     {
//         colIndex: 9,
//         selector: '#filterHasWhatsapp',
//         type: 'checkbox',
//         valueIfChecked: 'Yes'
//     },
//     {
//         colIndex: 10,
//         selector: '#filterHasWechat',
//         type: 'checkbox',
//         valueIfChecked: 'Yes'
//     },
//     {
//         colIndex: 11,
//         selector: '#filterStatus',
//         type: 'text'
//     },
//     ];


//     // Apply when typing or changing input
//     $('.filter-input').on('keyup change', function () {
//         applyDataTableFilters(table, customerFilters);
//     });

//     // Apply when clicking "Apply Filters"
//     $('#applyFilters').on('click', function () {
//         applyDataTableFilters(table, customerFilters);
//     });

//     // Reset filters
//     $('#resetFilters').on('click', function () {
//         $('.filter-input').val('').prop('checked', false).trigger('change');
//         table.search('').columns().search('');
//         table.draw();
//     });


// });