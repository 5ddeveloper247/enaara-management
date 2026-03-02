<?php

namespace App\Http\Controllers;

use App\Services\ShiftTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftTypesController extends Controller
{
    public function __construct(
        private ShiftTypeService $shiftTypeService
    ) {}

    public function index(): View
    {
        $shiftTypes = $this->shiftTypeService->getList();
        $organizations = $this->shiftTypeService->getOrganizationsForFilter();
        $counts = $this->shiftTypeService->getCounts();

        return view('admin.shift-type.index', [
            'shiftTypes' => $shiftTypes,
            'organizations' => $organizations,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['is_active' => 'required|boolean']);
        $shiftType = $this->shiftTypeService->updateStatus($id, (bool) $request->input('is_active'));
        if (!$shiftType) {
            return response()->json(['success' => false, 'message' => 'Shift type not found.'], 404);
        }
        return response()->json([
            'success' => true,
            'is_active' => $shiftType->is_active,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->shiftTypeService->delete($id);
        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Shift type not found.'], 404);
        }
        return response()->json(['success' => true]);
    }
}
