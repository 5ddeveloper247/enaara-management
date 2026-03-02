<?php

namespace App\Http\Controllers;

use App\Services\AttendanceModesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceModesController extends Controller
{
    public function __construct(
        private AttendanceModesService $attendanceModesService
    ) {}

    public function index(): View
    {
        $attendanceModes = $this->attendanceModesService->getList();
        $organizations = $this->attendanceModesService->getOrganizationsForFilter();
        $counts = $this->attendanceModesService->getCounts();

        return view('admin.attendance-modes.index', [
            'attendanceModes' => $attendanceModes,
            'organizations' => $organizations,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['is_active' => 'required|boolean']);
        $model = $this->attendanceModesService->updateStatus($id, (bool) $request->input('is_active'));
        if (!$model) {
            return response()->json(['success' => false, 'message' => 'Attendance mode not found.'], 404);
        }
        return response()->json([
            'success' => true,
            'is_active' => $model->is_active,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->attendanceModesService->delete($id);
        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Attendance mode not found.'], 404);
        }
        return response()->json(['success' => true]);
    }
}
