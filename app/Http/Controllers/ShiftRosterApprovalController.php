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

        $requests = $this->approvalService
            ->getPendingForApprover($user->employee_id ? (int) $user->employee_id : null)
            ->map(fn ($request) => $this->approvalService->formatPendingListItem($request))
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
