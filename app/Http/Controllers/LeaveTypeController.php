<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function __construct(
        private LeaveTypeService $leaveTypeService
    ) {}

    public function index(): View
    {
        $leaveTypes = $this->leaveTypeService->getList();
        $organizations = $this->leaveTypeService->getOrganizationsForFilter();
        $counts = $this->leaveTypeService->getCounts();

        return view('admin.leave-type.index', [
            'leaveTypes' => $leaveTypes,
            'organizations' => $organizations,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function create(): View|\Illuminate\Http\JsonResponse
    {
        $organizations = $this->leaveTypeService->getOrganizationsForFilter();
        
        if (request()->expectsJson()) {
            return response()->json([
                'organizations' => $organizations,
            ]);
        }

        return view('admin.leave-type.create', [
            'organizations' => $organizations,
        ]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'organization_id' => 'required|exists:organizations,id',
                'department_id' => 'nullable|exists:departments,id',
                'name' => 'required|string|max:255',
                'code' => [
                    'nullable',
                    'string',
                    'max:64',
                    Rule::unique('leave_types')->where('organization_id', $request->input('organization_id')),
                ],
                'annual_quota' => 'required|numeric|min:0|max:999.99',
                'is_active' => 'boolean',
            ]);

            $validated['is_active'] = $request->boolean('is_active');

            $leaveType = $this->leaveTypeService->create($validated);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Leave type created successfully.',
                    'leaveType' => $leaveType,
                ]);
            }

            return redirect()->route('admin.leave.type.index')
                ->with('success', 'Leave type created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }
    }

    public function edit(int $id): View|\Illuminate\Http\JsonResponse
    {
        $leaveType = $this->leaveTypeService->findById($id);

        if (!$leaveType instanceof LeaveType) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Leave type not found'], 404);
            }
            abort(404);
        }
        
        if (request()->expectsJson()) {
            $organizations = $this->leaveTypeService->getOrganizationsForFilter();
            
            return response()->json([
                'leaveType' => $leaveType,
                'organizations' => $organizations,
            ]);
        }

        $organizations = $this->leaveTypeService->getOrganizationsForFilter();

        return view('admin.leave-type.edit', [
            'leaveType' => $leaveType,
            'organizations' => $organizations,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $leaveType = $this->leaveTypeService->findById($id);

        if (!$leaveType instanceof LeaveType) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Leave type not found'], 404);
            }
            abort(404);
        }
        
        try {
            $validated = $request->validate([
                'organization_id' => 'required|exists:organizations,id',
                'department_id' => 'nullable|exists:departments,id',
                'name' => 'required|string|max:255',
                'code' => [
                    'nullable',
                    'string',
                    'max:64',
                    Rule::unique('leave_types')
                        ->where('organization_id', $request->input('organization_id'))
                        ->ignore($leaveType->id),
                ],
                'annual_quota' => 'required|numeric|min:0|max:999.99',
                'is_active' => 'boolean',
            ]);

            $validated['is_active'] = $request->boolean('is_active');

            $this->leaveTypeService->update($leaveType, $validated);
            
            if ($request->expectsJson()) {
                $updatedLeaveType = $this->leaveTypeService->findById($id);
                return response()->json([
                    'success' => true,
                    'message' => 'Leave type updated successfully.',
                    'leaveType' => $updatedLeaveType,
                ]);
            }

            return redirect()->route('admin.leave.type.index')
                ->with('success', 'Leave type updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }
    }
}

