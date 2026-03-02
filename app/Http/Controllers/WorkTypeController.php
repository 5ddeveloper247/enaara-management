<?php

namespace App\Http\Controllers;

use App\Services\WorkTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkTypeController extends Controller
{
    public function __construct(
        private WorkTypeService $workTypeService
    ) {}

    public function index(): View
    {
        $workTypes = $this->workTypeService->getList();
        $organizations = $this->workTypeService->getOrganizationsForFilter();
        $counts = $this->workTypeService->getCounts();

        return view('admin.work-type.index', [
            'workTypes' => $workTypes,
            'organizations' => $organizations,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['is_active' => 'required|boolean']);
        $workModel = $this->workTypeService->updateStatus($id, (bool) $request->input('is_active'));
        if (!$workModel) {
            return response()->json(['success' => false, 'message' => 'Work type not found.'], 404);
        }
        return response()->json([
            'success' => true,
            'is_active' => $workModel->is_active,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->workTypeService->delete($id);
        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Work type not found.'], 404);
        }
        return response()->json(['success' => true]);
    }
}
