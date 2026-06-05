<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    window.rosterExportExcelUrl = @json(route('admin.shift-roster.export-excel'));
    window.rosterExportExcelDepartmentsUrl = @json(route('admin.shift-roster.export-excel.departments'));
</script>
<script src="{{ asset('js/roster-export-excel.js') }}"></script>
