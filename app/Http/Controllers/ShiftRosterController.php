<?php

namespace App\Http\Controllers;

use App\Models\ShiftRosterEntry;
use App\Models\ShiftRosterAssignment;
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

    private function canAccessShiftPlannerRoster(): bool
    {
        return validatePermissions('admin/shift-planner') || validatePermissions('admin/shift-roster');
    }

    public function grid(Request $request)
    {
        if (! $this->canAccessShiftPlannerRoster()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('n'));
        $week = (int) $request->query('week', 1);

        try {
            $data = $this->shiftRosterService->getGridData($year, $month, $week);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        if (!validatePermissions('admin/shift-roster')) {
            abort(403, 'Unauthorized action.');
        }

        $rosters = ShiftRosterEntry::with(['employee', 'shift', 'assignment'])
            ->whereHas('employee', fn ($q) => $q->where('engagement_mode', 'shifts'))
            ->orderBy('roster_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $employees = Employee::where('is_active', 1)
            ->shiftBasedWorkArrangement()
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
            $roster = ShiftRosterEntry::with(['employee', 'shift', 'assignment'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'roster' => $roster,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Roster entry not found.'
            ], 404);
        }
    }

    public function store(ShiftRosterRequest $request)
    {
        if (!validatePermissions('admin/shift-roster')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $this->shiftRosterService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Shift assignment request submitted.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shift assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(ShiftRosterRequest $request, $id)
    {
        if (! $this->canAccessShiftPlannerRoster()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $this->shiftRosterService->update($request->validated(), $id);

            return response()->json([
                'success' => true,
                'message' => 'Shift roster entry updated successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shift roster entry: ' . $e->getMessage(),
            ], 500);
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
                'message' => 'Shift roster entry deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shift roster entry: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkAssign(BulkShiftRosterRequest $request)
    {
        if (! $this->canAccessShiftPlannerRoster()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $result = $this->shiftRosterService->bulkAssign($request->validated());

            return response()->json($result, $result['success'] ? 200 : 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign shifts: ' . $e->getMessage(),
            ], 500);
        }
    }
}