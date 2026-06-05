<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ShiftRoster\ShiftRosterExcelExportRequest;
use App\Services\ShiftRosterExcelExportService;

class ShiftRosterExcelExportController extends Controller
{
    public function __construct(
        private readonly ShiftRosterExcelExportService $excelExportService
    ) {
    }

    public function __invoke(ShiftRosterExcelExportRequest $request)
    {
        if (! $this->canAccessShiftPlannerRoster()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        try {
            $payload = $this->excelExportService->buildPayload($request->validated());

            if ($payload['rows'] === []) {
                return response()->json([
                    'success' => false,
                    'message' => 'No roster records found for the selected period.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => $payload,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export shift roster: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function canAccessShiftPlannerRoster(): bool
    {
        return validatePermissions('admin/shift-planner') || validatePermissions('admin/shift-roster');
    }
}
