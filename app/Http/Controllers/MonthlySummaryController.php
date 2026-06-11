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

    public function saveWorkAssignment(Request $request, int $employeeId)
    {
        $validated = $request->validate([
            'assignment_date' => ['required', 'date'],
            'work_type' => ['required', 'in:none,work_from_home,outstation'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $result = $this->monthlySummaryService->saveEmployeeWorkAssignment(
                $employeeId,
                $validated['assignment_date'],
                $validated['work_type'],
                $validated['notes'] ?? null,
            );

            $month = \Carbon\Carbon::parse($validated['assignment_date'])->format('Y-m');
            $calendar = $this->monthlySummaryService->getEmployeeMonthlyCalendar($employeeId, $month);

            return response()->json([
                'success' => true,
                'message' => ($validated['work_type'] === 'none')
                    ? 'Work assignment removed.'
                    : 'Work assignment saved.',
                'data' => [
                    'assignment' => $result['assignment'] ?? null,
                    'calendar' => $calendar,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to save work assignment.',
            ], 500);
        }
    }
}
