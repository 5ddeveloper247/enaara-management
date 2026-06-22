<?php

namespace App\Http\Controllers;

use App\Services\ShiftRosterApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ShiftRosterApprovalController extends Controller
{
    public function __construct(
        private readonly ShiftRosterApprovalService $approvalService
    ) {
    }

    public function pending(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $viewerEmployee = $user->employee;
        if (! $viewerEmployee) {
            return response()->json([
                'success' => true,
                'data' => [],
                'count' => 0,
            ]);
        }

        $requests = $this->approvalService
            ->getPendingForDashboardViewer($viewerEmployee, $user)
            ->map(fn (array $item) => $this->approvalService->formatPendingListItem(
                $item['request'],
                $item['segment'] ?? null
            ))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => $requests,
            'count' => count($requests),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $payload = $this->approvalService->buildDetailPayload(
                $id,
                $user?->employee_id ? (int) $user->employee_id : null
            );

            return response()->json([
                'success' => true,
                'data' => $payload,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Unable to load roster request.',
            ], 422);
        }
    }

    public function approve(int $id): JsonResponse
    {
        try {
            $request = $this->approvalService->approve($id, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Shift roster approved and assigned successfully.',
                'data' => [
                    'id' => $request->id,
                    'approval_status' => $request->approval_status,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Unable to approve roster request.',
            ], 422);
        }
    }

    public function applyForApproval(Request $request): JsonResponse
    {
        if (! validatePermissions('admin/shift-planner') && ! validatePermissions('admin/shift-roster')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $validated = $request->validate([
                'year' => ['required', 'integer', 'min:2000', 'max:2100'],
                'month' => ['required', 'integer', 'min:1', 'max:12'],
                'employee_group' => ['nullable', 'in:internal,third_party'],
            ]);

            $year = (int) $validated['year'];
            $month = (int) $validated['month'];
            $employeeGroup = $validated['employee_group'] ?? 'internal';
            $hadPendingRequest = \App\Models\ShiftRosterApprovalRequest::query()
                ->where('approval_status', 'pending')
                ->where('request_type', 'roster')
                ->where('shift_label', \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') . ' Roster'
                    . ($employeeGroup === 'third_party' ? ' (Third-party)' : ''))
                ->exists();

            $approvalRequest = $this->approvalService->submitPendingEntriesForApproval(
                $year,
                $month,
                $employeeGroup
            );

            return response()->json([
                'success' => true,
                'message' => $hadPendingRequest
                    ? 'Additional shifts were added to the pending GM approval request.'
                    : 'Roster submitted to GM for approval.',
                'data' => [
                    'request_count' => 1,
                    'request_ids' => [$approvalRequest->id],
                    'request_id' => $approvalRequest->id,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Unable to submit roster for approval.',
            ], 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => ['nullable', 'string', 'max:1000'],
            ]);

            $approvalRequest = $this->approvalService->reject($id, $validated['reason'] ?? null, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Shift roster request rejected.',
                'data' => [
                    'id' => $approvalRequest->id,
                    'approval_status' => $approvalRequest->approval_status,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Unable to reject roster request.',
            ], 422);
        }
    }
}
