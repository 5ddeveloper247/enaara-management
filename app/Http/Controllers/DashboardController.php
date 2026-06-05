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

    public function pendingApprovals(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getPendingApprovals();
            return response()->json(['success' => true, 'data' => $data, 'count' => count($data)]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function pendingRosterApprovals(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getPendingRosterApprovals();
            return response()->json(['success' => true, 'data' => $data, 'count' => count($data)]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function upcomingHolidays(Request $request): JsonResponse
    {
        try {
            $days = in_array((int) $request->query('period'), [7, 14]) ? (int) $request->query('period') : 7;
            $data = $this->dashboardService->getUpcomingHolidays($days);
            return response()->json(['success' => true, 'data' => $data, 'count' => count($data)]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function whoIsOutToday(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getWhoIsOutToday();
            return response()->json(['success' => true, 'data' => $data, 'count' => count($data)]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
