<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): View
    {
        return $this->dashboardService->index();
    }

    public function attendanceChart(Request $request): JsonResponse
    {
        try {
            $days = in_array((int) $request->query('period'), [7, 14]) ? (int) $request->query('period') : 7;
            $data = $this->dashboardService->getAttendanceChartData($days);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
