<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Employee\EmployeeStoreRequest;
use App\Http\Requests\Admin\Employee\EmployeeUpdateRequest;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Models\Employee;

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
        if (!validatePermissions('admin/employees')) {
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
        if (! validatePermissions('admin/employees')) {
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
        if (!validatePermissions('admin/employees')) {
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

    public function saveStep(\App\Http\Requests\Admin\Employee\EmployeeStepRequest $request)
    {
        if (!validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $step = (int) $request->input('step');
            $employeeId = $request->input('employee_id');
            $data = $request->except(['_token', 'employee_id', 'kept_attachment_ids', 'attachments', 'profile_photo']);

            $photos   = $request->hasFile('profile_photo') ? [$request->file('profile_photo')] : [];
            $attachments = $this->extractAttachments($request);
            $keptAttachmentIds = array_values(array_filter(array_map('intval', $request->input('kept_attachment_ids', []))));

            $moduleNames = [
                1 => 'General Information',
                2 => 'Employment Information',
                3 => 'Verification Details',
                4 => 'Armed Forces Details',
                5 => 'Bank Details',
                6 => 'Contact & Account Details',
            ];
            $moduleName = $moduleNames[$step] ?? "Step $step";

            if ($step === 1 && !$employeeId) {
                // Initial creation
                $employee = $this->employeeService->store($data, $photos, $attachments);
                $message = $moduleName . ' saved successfully.';
            } else {
                // Update existing record
                $employee = $this->employeeService->update((int)$employeeId, $data, $photos, $attachments, $keptAttachmentIds);
                $message = $moduleName . ' saved successfully.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'employee_id' => $employee->id,
                'next_step' => $step + 1
            ]);
        } catch (\Exception $e) {
            Log::error('Employee saveStep failed', ['step' => $request->input('step'), 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function saveSubsection(\App\Http\Requests\Admin\Employee\EmployeeStepRequest $request)
    {
        if (!validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $employeeId = $request->input('employee_id');
            $subsection = $request->input('subsection');
            $data = $request->all();

            if (!$employeeId) {
                return response()->json(['success' => false, 'message' => 'Employee ID is required.'], 422);
            }

            switch ($subsection) {
                case 'contact':
                    $this->employeeService->saveContact((int)$employeeId, $data);
                    // Also update main employee record if email was provided
                    if (!empty($data['contact_email'])) {
                        Employee::where('id', $employeeId)->update(['email' => $data['contact_email']]);
                    }
                    $message = 'Contact Information saved successfully.';
                    break;
                case 'family_row':
                    $record = $this->employeeService->saveFamilyMember((int)$employeeId, $data);
                    $message = 'Family member added successfully.';
                    $responseData = ['id' => $record?->id];
                    break;
                case 'academic_row':
                    $record = $this->employeeService->saveAcademic((int)$employeeId, $data);
                    $message = 'Academic record added successfully.';
                    $responseData = ['id' => $record?->id];
                    break;
                case 'employment_row':
                    $record = $this->employeeService->saveExEmployment((int)$employeeId, $data);
                    $message = 'Employment history record added successfully.';
                    $responseData = ['id' => $record?->id];
                    break;
                case 'medical':
                    $this->employeeService->saveMedical((int)$employeeId, $data);
                    $message = 'Medical Information saved successfully.';
                    break;
                case 'references':
                    $this->employeeService->saveReferences((int)$employeeId, $data);
                    $message = 'References saved successfully.';
                    break;
                case 'photo':
                    if ($request->hasFile('profile_photo')) {
                        $this->employeeService->savePhoto((int)$employeeId, $request->file('profile_photo'));
                        $message = 'Profile photo saved successfully.';
                    } else {
                        return response()->json(['success' => false, 'message' => 'No photo uploaded.'], 422);
                    }
                    break;
                case 'bank_row':
                    $validated = $request->validated();
                    $row = Arr::only($validated, [
                        'account_category', 'account_title', 'account_no', 'bank_name',
                        'branch_code', 'branch_address', 'iban', 'account_type', 'is_salary_account',
                    ]);
                    try {
                        $record = $this->employeeService->saveBankDetailRow(
                            (int) $employeeId,
                            $row,
                            ! empty($validated['bank_detail_id']) ? (int) $validated['bank_detail_id'] : null
                        );
                    } catch (\InvalidArgumentException $e) {
                        return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
                    }
                    $message = 'Bank account saved successfully.';
                    $responseData = [
                        'bank' => [
                            'id'                 => $record->id,
                            'account_category'   => $record->account_category,
                            'account_title'      => $record->account_title,
                            'account_no'         => $record->account_no,
                            'bank_name'          => $record->bank_name,
                            'branch_code'        => $record->branch_code,
                            'branch_address'     => $record->branch_address,
                            'iban'               => $record->iban,
                            'account_type'       => $record->account_type,
                            'is_salary_account'  => (bool) $record->is_salary_account,
                        ],
                        'salary_bank_id' => $this->employeeService->salaryBankIdForEmployee((int) $employeeId),
                    ];
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid subsection type.'], 422);
            }

            return response()->json(array_merge([
                'success' => true,
                'message' => $message,
                'employee_id' => $employeeId,
                'subsection' => $subsection
            ], $responseData ?? []));

        } catch (\Exception $e) {
            Log::error('Employee saveSubsection failed', ['subsection' => $request->input('subsection'), 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteFamily(Request $request)
    {
        return $this->processDeletion($request, 'family_row');
    }

    public function deleteAcademic(Request $request)
    {
        return $this->processDeletion($request, 'academic_row');
    }

    public function deleteEmployment(Request $request)
    {
        return $this->processDeletion($request, 'employment_row');
    }

    public function deleteBankDetail(Request $request)
    {
        if (! validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $validated = $request->validate([
                'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
                'id'          => [
                    'required',
                    'integer',
                    Rule::exists('employee_bank_details', 'id')->where('employee_id', (int) $request->input('employee_id')),
                ],
            ]);
            $deleted = $this->employeeService->deleteBankDetailRow((int) $validated['employee_id'], (int) $validated['id']);
            if (! $deleted) {
                return response()->json(['success' => false, 'message' => 'Record not found or already deleted.'], 404);
            }

            return response()->json([
                'success'        => true,
                'message'        => 'Bank account deleted successfully.',
                'salary_bank_id' => $this->employeeService->salaryBankIdForEmployee((int) $validated['employee_id']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('deleteBankDetail failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deletePhoto(Request $request)
    {
        if (!validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $id = $request->input('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Validation failed. Missing ID.'], 422);
        }

        try {
            $success = $this->employeeService->deletePhoto((int)$id);
            if ($success) {
                return response()->json(['success' => true, 'message' => 'Profile photo deleted successfully.']);
            }
            return response()->json(['success' => false, 'message' => 'Record not found or already deleted.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function saveAttachment(\App\Http\Requests\Admin\Employee\EmployeeStepRequest $request)
    {
        $employeeId = $request->input('employee_id');
        if (!$employeeId) {
            return response()->json(['success' => false, 'message' => 'Employee must be saved before adding attachments.'], 422);
        }

        try {
            $attachments = $request->input('attachments', []);
            $attachmentData = $attachments[0] ?? [];
            
            if (!$request->hasFile('attachments.0.files')) {
                return response()->json(['success' => false, 'message' => 'No files were uploaded.'], 422);
            }

            $attachmentData['files'] = $request->file('attachments.0.files');
            $savedFiles = $this->employeeService->saveSingleAttachment((int)$employeeId, $attachmentData);
            
            if (empty($savedFiles)) {
                return response()->json(['success' => false, 'message' => 'No files could be saved.'], 500);
            }

            // Format for JS response
            $formattedFiles = array_map(function($f) {
                return [
                    'id' => $f->id,
                    'name' => $f->file_name,
                    'mime_type' => $f->mime_type,
                    'url' => asset('storage/' . $f->file_path),
                ];
            }, $savedFiles);

            return response()->json([
                'success' => true,
                'message' => 'Attachment saved successfully.',
                'files' => $formattedFiles,
                'attachment_id' => $savedFiles[0]->id ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Employee saveAttachment failed', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAttachment(Request $request)
    {
        if (!validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $id = $request->input('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Missing attachment ID.'], 422);
        }

        try {
            $success = $this->employeeService->deleteAttachment((int)$id);
            if ($success) {
                return response()->json(['success' => true, 'message' => 'Attachment deleted successfully.']);
            }
            return response()->json(['success' => false, 'message' => 'Attachment not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    private function processDeletion(Request $request, string $type)
    {
        if (!validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $id = $request->input('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Validation failed. Missing ID.'], 422);
        }

        try {
            $success = $this->employeeService->deleteSubsectionRow($type, (int)$id);
            if ($success) {
                return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
            }
            return response()->json(['success' => false, 'message' => 'Record not found or already deleted.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, int $id)
    {
        if (!validatePermissions('admin/employees')) {
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
