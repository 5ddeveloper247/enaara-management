<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MonthlySummaryPdfExportService
{
    public function __construct(
        private readonly MonthlySummaryService $monthlySummaryService,
    ) {}

    public function download(Request $request): Response
    {
        $report = $this->monthlySummaryService->buildExportReport($request);

        $filename = sprintf('monthly-summary-%s.pdf', $report['period_slug']);

        $pdf = Pdf::loadView('exports.monthly-summary.report-pdf', $report)
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function downloadEmployee(int $employeeId, Request $request): Response
    {
        $month = $request->get('month', now()->format('Y-m'));
        $report = $this->monthlySummaryService->buildEmployeeExportReport($employeeId, $month);

        $employeeCode = preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string) ($report['employee']['employee_code'] ?? 'employee'));
        $filename = sprintf(
            'monthly-summary-%s-%s.pdf',
            $report['period_slug'],
            trim($employeeCode, '-')
        );

        $pdf = Pdf::loadView('exports.monthly-summary.employee-report-pdf', $report)
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
