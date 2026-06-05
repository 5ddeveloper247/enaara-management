@php
    $printInfo = $print_info ?? [];
    $printedBy = trim((string) ($printInfo['printed_by_name'] ?? ''));
    $printedDate = $printInfo['printed_at_date'] ?? '';
    $printedTime = $printInfo['printed_at_time'] ?? '';
@endphp

@if($printedDate !== '' || $printedTime !== '' || $printedBy !== '')
    <div class="print-info">
        Printed by: {{ $printedBy !== '' ? $printedBy : '—' }}
        | Date: {{ $printedDate }}
        | Time: {{ $printedTime }}
    </div>
@endif
