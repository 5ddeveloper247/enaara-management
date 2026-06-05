<script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.min.js"></script>
<script>
    window.rosterExportExcelUrl = @json(route('admin.shift-roster.export-excel'));
    window.rosterExportExcelDepartmentsUrl = @json(route('admin.shift-roster.export-excel.departments'));
</script>
<script src="{{ asset('js/roster-export-excel.js') }}"></script>
