<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\User\UserStoreRequest;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return $this->userService->index();
    }

    public function data(): JsonResponse
    {
        try {
            return response()->json($this->userService->getTableData());
        } catch (\Exception $e) {
            Log::error('User table data failed', ['error' => $e->getMessage()]);
            return response()->json(['data' => []], 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'stats'   => $this->userService->getStats(),
            ]);
        } catch (\Exception $e) {
            Log::error('User stats failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'stats' => []], 500);
        }
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'User "' . $user->name . '" created successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('User store failed', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->update($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'User "' . $user->name . '" updated successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('User update failed', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $isActive = (bool) $request->input('is_active', false);
            $user     = $this->userService->updateStatus($id, $isActive);

            return response()->json([
                'success' => true,
                'message' => 'User status updated.',
                'is_active' => $user->is_active,
            ]);
        } catch (\Exception $e) {
            Log::error('User status update failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->destroy($id);
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('User delete failed', ['user_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }
}
