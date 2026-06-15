<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LeaveCalenderService;
use App\Http\Requests\Admin\LeaveCalendar\LeaveCalendarRequest;
use App\Models\Organization;
use App\Models\Department;
use App\Models\Sbu;
use App\Models\PublicHoliday;

class LeaveCalendarController extends Controller
{
    public function __construct(LeaveCalenderService $leaveCalenderService)
    {
        $this->leaveCalenderService = $leaveCalenderService;
    }

    public function index()
    {
        if (!validatePermissions('admin/leave-calendar')) {
            abort(403, 'Unauthorized action.');
        }
        $organizations = Organization::all();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $sbus = Sbu::where('is_active', true)->orderBy('name')->get();
        $publicHolidays = PublicHoliday::with('organizations', 'departments', 'sbus')->get();

        // Fetch dynamic calendar data
        $blackoutDates = $this->leaveCalenderService->getBlackoutDates();
        $deptLeaves = $this->leaveCalenderService->getDepartmentLeaves(
            now()->subMonths(3)->startOfMonth(),
            now()->addMonths(3)->endOfMonth()
        );

        return view('admin.leave-calendar.index', compact(
            'organizations',
            'departments',
            'sbus',
            'publicHolidays',
            'blackoutDates',
            'deptLeaves'
        ));
    }

    /**
     * Fetch real employee list for a specific department and date (AJAX)
     */
    public function fetchDepartmentLeaveEmployees(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'department_id' => 'required|integer',
        ]);

        try {
            $employees = $this->leaveCalenderService->getDepartmentLeaveDetails(
                $request->date,
                $request->department_id
            );

            return response()->json([
                'success' => true,
                'employees' => $employees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employees: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!validatePermissions('admin/leave-calendar/add') &&
            !validatePermissions('admin/leave-calendar/update/{id}') &&
            !validatePermissions('admin/leave-calendar/destroy/{id}')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $holiday = PublicHoliday::with('organizations','departments', 'sbus')->findOrFail($id);
            return response()->json([
                'success' => true,
                'holiday' => $holiday,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Holiday not found.'], 404);
        }
    }

    public function store(LeaveCalendarRequest $request)
    {
        if (!validatePermissions('admin/leave-calendar/add')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->leaveCalenderService->store($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Holiday added successfully.',
                ]);
            }

            return redirect()
                ->route('admin.leave-calendar.index')
                ->with('success', 'Holiday added successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add holiday: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to add holiday: ' . $e->getMessage());
        }
    }


    public function update(LeaveCalendarRequest $request, $id)
    {
        if (!validatePermissions('admin/leave-calendar/update/{id}')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->leaveCalenderService->update($request->validated(), $id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Holiday updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.leave-calendar.index')
                ->with('success', 'Holiday updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update holiday: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update holiday: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!validatePermissions('admin/leave-calendar/destroy/{id}')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $this->leaveCalenderService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Holiday deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete holiday: ' . $e->getMessage(),
            ], 500);
        }
    }
}
