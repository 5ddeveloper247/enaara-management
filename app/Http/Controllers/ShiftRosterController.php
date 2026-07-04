<?php

namespace App\Http\Controllers;

use App\Models\ShiftRosterEntry;
use App\Models\ShiftRosterApprovalRequest;
use App\Models\ShiftRosterAssignment;
use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Services\ShiftPlannerService;
use App\Services\ShiftRosterAuditHistoryService;
use App\Services\ShiftRosterPdfExportService;
use App\Services\ShiftRosterService;
use App\Http\Requests\Admin\ShiftRoster\ShiftRosterPdfExportRequest;
use App\Http\Requests\Admin\ShiftRoster\ShiftRosterRequest;
use App\Http\Requests\Admin\ShiftRoster\BulkShiftRosterRequest;
use App\Http\Requests\Admin\ShiftRoster\ShiftRosterFloorOptionsRequest;
use App\Http\Requests\Admin\ShiftRoster\BulkShiftRosterFloorOptionsRequest;
use Illuminate\Http\Request;

class ShiftRosterController extends Controller
{
    protected $shiftRosterService;

    protected $shiftRosterPdfExportService;

    public function __construct(
        ShiftRosterService $shiftRosterService,
        ShiftRosterPdfExportService $shiftRosterPdfExportService,
        private readonly ShiftPlannerService $shiftPlannerService,
    ) {
        $this->shiftRosterService = $shiftRosterService;
        $this->shiftRosterPdfExportService = $shiftRosterPdfExportService;
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
        $filter = $request->query('filter', 'internal');
        $includeDeleted = filter_var($request->query('include_deleted'), FILTER_VALIDATE_BOOLEAN);
        $approvalRequestId = $request->query('approval_request_id')
            ? (int) $request->query('approval_request_id')
            : null;

        try {
            $data = $this->shiftRosterService->getGridData(
                $year,
                $month,
                $week,
                $filter,
                $includeDeleted,
                $approvalRequestId
            );

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

    public function exportPdf(ShiftRosterPdfExportRequest $request)
    {
        if (! $this->canAccessShiftPlannerRoster()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        try {
            return $this->shiftRosterPdfExportService->download($request->validated());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export shift roster PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function floorOptions(ShiftRosterFloorOptionsRequest $request)
    {
        if (! $this->canAccessShiftPlannerRoster()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        try {
            $validated = $request->validated();

            return response()->json([
                'success' => true,
                'data' => $this->shiftRosterService->floorOptionsForAssignee(
                    $validated['employee_type'],
                    (int) $validated['employee_id']
                ),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function bulkFloorOptions(BulkShiftRosterFloorOptionsRequest $request)
    {
        if (! $this->canAccessShiftPlannerRoster()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        try {
            return response()->json([
                'success' => true,
                'data' => $this->shiftRosterService->floorOptionsForBulkAssignees(
                    $request->validated()['employee_ids']
                ),
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

        $rosters = ShiftRosterEntry::with(['employee', 'outsourcedEmployee', 'shift', 'assignment'])
            ->where(function ($query) {
                $query->whereHas('employee', fn ($q) => $q->where('engagement_mode', 'shifts'))
                    ->orWhereHas('outsourcedEmployee');
            })
            ->orderBy('roster_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $employees = Employee::where('is_active', 1)
            ->shiftBasedWorkArrangement()
            ->excludeTerminated()
            ->orderBy('full_name')
            ->get();
        $outsourcedEmployees = OutsourcedEmployee::with('contractorCompany')
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get();
        $shifts = $this->shiftPlannerService->getActiveList();

        return view('admin.shift-planner.roster', compact('rosters', 'employees', 'outsourcedEmployees', 'shifts'));
    }

    public function changeHistory($id, ShiftRosterAuditHistoryService $auditHistoryService)
    {
        if (! $this->canAccessShiftPlannerRoster()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        try {
            return response()->json([
                'success' => true,
                'data' => $auditHistoryService->forEntry((int) $id),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Roster entry not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not load change history.',
            ], 500);
        }
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
                'message' => 'Shift saved as pending. Use Apply for Approval when ready.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
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
            $validated = $request->validated();
            $entryBefore = ShiftRosterEntry::query()->find((int) $id);
            $wasPendingApproval = $entryBefore
                && $entryBefore->shift_roster_approval_request_id
                && ShiftRosterApprovalRequest::query()
                    ->whereKey($entryBefore->shift_roster_approval_request_id)
                    ->where('approval_status', 'pending')
                    ->exists();
            $markAsOff = filter_var($validated['mark_as_off'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $wasAlreadyOff = $markAsOff
                && $entryBefore
                && strtolower((string) $entryBefore->status) === 'off';

            $this->shiftRosterService->update($validated, $id);
            $pendingSuffix = $wasPendingApproval ? ' The pending GM approval request has been updated.' : '';

            return response()->json([
                'success' => true,
                'message' => $markAsOff
                    ? ($wasAlreadyOff
                        ? 'This day is already marked as off.' . $pendingSuffix
                        : 'Day marked as off.' . $pendingSuffix)
                    : 'Shift updated.' . ($wasPendingApproval ? $pendingSuffix : ' Saved as pending.'),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
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
                'message' => 'Shift removed from roster.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
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
