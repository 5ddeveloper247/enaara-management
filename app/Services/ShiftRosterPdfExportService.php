<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ShiftRosterPdfExportService
{
    public function __construct(
        protected ShiftRosterService $shiftRosterService
    ) {}

    public function download(array $options): Response
    {
        $report = $this->shiftRosterService->buildMonthlyExportReport($options);
        $filename = sprintf(
            'shift-roster-%s-%s.pdf',
            strtolower($report['period_slug']),
            $options['employee_group']
        );

        $pdf = Pdf::loadView('exports.shift-roster.monthly-report-pdf', $report)
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
