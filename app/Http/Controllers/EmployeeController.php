<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Employee\EmployeeStoreRequest;
use App\Http\Requests\Admin\Employee\EmployeeUpdateRequest;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    private EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index()
    {
        return $this->employeeService->index();
    }

    public function store(EmployeeStoreRequest $request)
    {
        if (!validatePermissions('admin/employee')) {
            Log::warning('Unauthorized employee create attempt', ['user_id' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action.'], 403);
        }

        try {
            Log::info('Employee store request received', ['user_id' => Auth::id(), 'name' => $request->full_name]);

            $photos   = $request->hasFile('profile_photo') ? [$request->file('profile_photo')] : [];
            $attachments = $this->extractAttachments($request);
            $employee = $this->employeeService->store($request->validated(), $photos, $attachments);

            Log::info('Employee stored successfully', ['employee_id' => $employee->id]);

            return response()->json([
                'success'     => true,
                'message'     => 'Employee "' . $employee->full_name . '" (Code: ' . $employee->employee_code . ') created successfully.',
                'redirect'    => route('admin.employee.index'),
                'employee_id' => $employee->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Employee store failed', ['user_id' => Auth::id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    public function tableData(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $this->employeeService->getTableData([
                    'filter_employee_type' => $request->query('filter_employee_type'),
                    'filter_organization'  => $request->query('filter_organization'),
                    'filter_sbu'           => $request->query('filter_sbu'),
                    'filter_department'    => $request->query('filter_department'),
                    'filter_name'          => $request->query('filter_name'),
                    'filter_cnic'          => $request->query('filter_cnic'),
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('Employee table data failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'data' => []], 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'stats'   => $this->employeeService->getStats(),
            ]);
        } catch (\Exception $e) {
            Log::error('Employee stats failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'stats' => []], 500);
        }
    }

    public function previewEmployeeCode(Request $request): JsonResponse
    {
        if (! validatePermissions('admin/employee')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action.'], 403);
        }

        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'role_id'         => ['required', 'integer', 'exists:roles,id'],
            'sbu_id'          => ['nullable', 'integer', 'exists:sbus,id'],
        ]);

        try {
            $code = $this->employeeService->previewNextEmployeeCode(
                (int) $validated['organization_id'],
                (int) $validated['role_id'],
                isset($validated['sbu_id']) ? (int) $validated['sbu_id'] : null
            );

            return response()->json(['success' => true, 'code' => $code]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function edit(int $id)
    {
        try {
            return $this->employeeService->edit($id);
        } catch (\Exception $e) {
            Log::error('Employee edit page failed', ['employee_id' => $id, 'error' => $e->getMessage()]);
            return redirect()->route('admin.employee.index')->with('error', 'Employee not found.');
        }
    }

    public function update(EmployeeUpdateRequest $request, int $id)
    {
        if (!validatePermissions('admin/employee')) {
            Log::warning('Unauthorized employee update attempt', ['user_id' => Auth::id(), 'employee_id' => $id]);
            return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action.'], 403);
        }

        try {
            Log::info('Employee update request received', ['user_id' => Auth::id(), 'employee_id' => $id]);

            $photos   = $request->hasFile('profile_photo') ? [$request->file('profile_photo')] : [];
            $attachments = $this->extractAttachments($request);
            $keptAttachmentIds = array_values(array_filter(array_map('intval', $request->input('kept_attachment_ids', []))));
            $employee = $this->employeeService->update($id, $request->validated(), $photos, $attachments, $keptAttachmentIds);

            Log::info('Employee updated successfully', ['employee_id' => $employee->id]);

            return response()->json([
                'success'  => true,
                'message'  => 'Employee "' . $employee->full_name . '" updated successfully.',
                'redirect' => route('admin.employee.index'),
            ]);

        } catch (\Exception $e) {
            Log::error('Employee update failed', ['user_id' => Auth::id(), 'employee_id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, int $id)
    {
        if (!validatePermissions('admin/employee')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $this->employeeService->destroy($id);
            return response()->json(['success' => true, 'message' => 'Employee deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Employee delete failed', ['employee_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }

    private function extractAttachments(Request $request): array
    {
        $rows = $request->input('attachments', []);
        $attachments = [];

        foreach ($rows as $idx => $row) {
            $files = $request->file("attachments.$idx.files", []);
            if (!is_array($files) || empty($files)) {
                continue;
            }

            $attachments[] = [
                'name' => trim((string) ($row['name'] ?? '')),
                'type' => trim((string) ($row['type'] ?? '')),
                'description' => trim((string) ($row['description'] ?? '')),
                'files' => $files,
            ];
        }

        return $attachments;
    }
}
