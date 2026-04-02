<?php

namespace App\Http\Controllers;

use App\Models\ShiftRoaster;
use App\Models\Employee;
use App\Models\ShiftPlanner;
use App\Services\ShiftRosterService;
use App\Http\Requests\Admin\ShiftRoster\ShiftRosterRequest;
use App\Http\Requests\Admin\ShiftRoster\BulkShiftRosterRequest;
use Illuminate\Http\Request;

class ShiftRosterController extends Controller
{
    protected $shiftRosterService;

    public function __construct(ShiftRosterService $shiftRosterService)
    {
        $this->shiftRosterService = $shiftRosterService;
    }

    public function index()
    {
        if (!validatePermissions('admin/shift-roster')) {
            abort(403, 'Unauthorized action.');
        }

        $rosters = ShiftRoaster::with(['employee', 'shift'])
            ->orderBy('roster_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $employees = Employee::where('is_active', 1)
            ->orderBy('full_name')
            ->get();
        $shifts = ShiftPlanner::where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('admin.shift-planner.roster', compact('rosters', 'employees', 'shifts'));
    }

    public function show($id)
    {
        if (!validatePermissions('admin/shift-roster')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $roster = ShiftRoaster::with(['employee', 'shift'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'roster' => $roster,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Roster not found.'
            ], 404);
        }
    }

    public function store(ShiftRosterRequest $request)
    {
        if (!validatePermissions('admin/shift-roster')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->shiftRosterService->store($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift roster created successfully.',
                ]);
            }

            return redirect()
                ->route('admin.shift-roster.index')
                ->with('success', 'Shift roster created successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create shift roster: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create shift roster: ' . $e->getMessage());
        }
    }

    public function update(ShiftRosterRequest $request, $id)
    {
        if (!validatePermissions('admin/shift-roster')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->shiftRosterService->update($request->validated(), $id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift roster updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.shift-roster.index')
                ->with('success', 'Shift roster updated successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update shift roster: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update shift roster: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!validatePermissions('admin/shift-roster')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $this->shiftRosterService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Shift roster deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shift roster: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkAssign(BulkShiftRosterRequest $request)
    {
        if (!validatePermissions('admin/shift-roster')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $result = $this->shiftRosterService->bulkAssign($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Shift roster assigned successfully.',
                    'data' => $result,
                ]);
            }

            return redirect()
                ->route('admin.shift-roster.index')
                ->with('success', 'Shift roster assigned successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign shift roster: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to assign shift roster: ' . $e->getMessage());
        }
    }
}