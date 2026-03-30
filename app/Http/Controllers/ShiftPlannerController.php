<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ShiftPlannerService;
use App\Http\Requests\Admin\ShiftPlanner\ShiftPlannerRequest;
use App\Models\ShiftPlanner;

class ShiftPlannerController extends Controller
{
    protected $shiftPlannerService;

    public function __construct(ShiftPlannerService $shiftPlannerService)
    {
        $this->shiftPlannerService = $shiftPlannerService;
    }

    public function index()
    {
        if (!validatePermissions('admin/shift-planner')) {
            abort(403, 'Unauthorized action.');
        }

        $shifts = ShiftPlanner::orderBy('updated_at', 'desc')->get();
        return view('admin.shift-planner.index', compact('shifts'));
    }

    public function show($id)
    {
        if (!validatePermissions('admin/shift-planner')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $shift = ShiftPlanner::findOrFail($id);

            return response()->json([
                'success' => true,
                'shift' => $shift,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Shift not found.'], 404);
        }
    }

    public function store(ShiftPlannerRequest $request)
    {
        if (!validatePermissions('admin/shift-planner')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->shiftPlannerService->store($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift created successfully.',
                ]);
            }

            return redirect()
                ->route('admin.shift-planner.index')
                ->with('success', 'Shift created successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create shift: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create shift: ' . $e->getMessage());
        }
    }

    public function update(ShiftPlannerRequest $request, $id)
    {
        if (!validatePermissions('admin/shift-planner')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->shiftPlannerService->update($request->validated(), $id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.shift-planner.index')
                ->with('success', 'Shift updated successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update shift: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update shift: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!validatePermissions('admin/shift-planner')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $this->shiftPlannerService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Shift deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shift: ' . $e->getMessage(),
            ], 500);
        }
    }
}