<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ShiftRosterPdfExportService
{
    public function __construct(
        protected ShiftRosterExportReportService $exportReportService
    ) {}

    public function download(array $options): Response
    {
        $layout = $options['export_layout'] ?? 'per_employee';

        if ($layout === 'per_employee') {
            $report = $this->exportReportService->buildPerEmployeeReport($options);
            $view = 'exports.shift-roster.per-employee-pdf';
            $filename = sprintf(
                'shift-roster-%s-%s-per-employee.pdf',
                strtolower($report['period_slug']),
                $options['employee_group'] ?? 'internal'
            );
            $orientation = 'portrait';
        } elseif ($layout === 'calendar') {
            $report = $this->exportReportService->buildCalendarReport($options);
            $view = 'exports.shift-roster.calendar-report-pdf';
            $filename = sprintf(
                'shift-roster-%s-%s-calendar.pdf',
                strtolower($report['period_slug']),
                $options['employee_group'] ?? 'internal'
            );
            $orientation = 'landscape';
        } else {
            $report = $this->exportReportService->buildTabularReport($options);
            $view = 'exports.shift-roster.monthly-report-pdf';
            $filename = sprintf(
                'shift-roster-%s-%s.pdf',
                strtolower($report['period_slug']),
                $options['employee_group'] ?? 'internal'
            );
            $orientation = 'portrait';
        }

        $paper = $layout === 'calendar' ? 'a3' : 'a4';

        $pdf = Pdf::loadView($view, $report)
            ->setPaper($paper, $orientation);

        return $pdf->download($filename);
    }
}
