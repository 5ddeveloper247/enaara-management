<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MonthlySummaryService;

class MonthlySummaryController extends Controller
{
    protected MonthlySummaryService $monthlySummaryService;

    public function __construct(MonthlySummaryService $monthlySummaryService)
    {
        $this->monthlySummaryService = $monthlySummaryService;
    }

    public function index(Request $request)
    {
        return $this->monthlySummaryService->index($request);
    }

    public function employeeCalendar(Request $request, int $employeeId)
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        try {
            $calendar = $this->monthlySummaryService->getEmployeeMonthlyCalendar(
                $employeeId,
                $validated['month'],
            );

            return response()->json([
                'success' => true,
                'data' => $calendar,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load monthly calendar.',
            ], 500);
        }
    }
}
