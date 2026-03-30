<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Workflow\WorkflowStoreRequest;
use App\Http\Requests\Admin\Workflow\WorkflowUpdateRequest;
use App\Services\WorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkflowController extends Controller
{
    public function __construct(private readonly WorkflowService $workflowService) {}

    // ── Index ────────────────────────────────────
    public function index()
    {
        return $this->workflowService->index();
    }

    // ── Table Data (AJAX) ────────────────────────
    public function data(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->workflowService->getTableData(),
        ]);
    }

    // ── Stats (AJAX) ─────────────────────────────
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->workflowService->getStats(),
        ]);
    }

    // ── Store ─────────────────────────────────────
    public function store(WorkflowStoreRequest $request): JsonResponse
    {
        try {
            $workflow = $this->workflowService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Workflow "' . $workflow->name . '" created successfully.',
                'data'    => ['id' => $workflow->id],
            ]);
        } catch (\Exception $e) {
            Log::error('Workflow store failed', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // ── Update ────────────────────────────────────
    public function update(int $id, WorkflowUpdateRequest $request): JsonResponse
    {
        try {
            $workflow = $this->workflowService->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Workflow "' . $workflow->name . '" updated successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Workflow update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // ── Update Status ─────────────────────────────
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status' => 'required|in:active,inactive']);

        try {
            $workflow = $this->workflowService->updateStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Workflow status updated to ' . $workflow->status . '.',
            ]);
        } catch (\Exception $e) {
            Log::error('Workflow status update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // ── Destroy ───────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->workflowService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Workflow deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Workflow delete failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }
}
