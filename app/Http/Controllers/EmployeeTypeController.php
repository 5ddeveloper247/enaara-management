<?php

namespace App\Http\Controllers;

use App\Services\EmployeeTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeTypeController extends Controller
{
    public function __construct(
        private EmployeeTypeService $employeeTypeService
    ) {}

    public function index(): View
    {
        $employeeTypes = $this->employeeTypeService->getList();
        $organizations = $this->employeeTypeService->getOrganizationsForFilter();
        $counts = $this->employeeTypeService->getCounts();

        return view('admin.employee-type.index', [
            'employeeTypes' => $employeeTypes,
            'organizations' => $organizations,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['is_active' => 'required|boolean']);
        $employeeType = $this->employeeTypeService->updateStatus($id, (bool) $request->input('is_active'));
        if (!$employeeType) {
            return response()->json(['success' => false, 'message' => 'Employee type not found.'], 404);
        }
        return response()->json([
            'success' => true,
            'is_active' => $employeeType->is_active,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->employeeTypeService->delete($id);
        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Employee type not found.'], 404);
        }
        return response()->json(['success' => true]);
    }
}
