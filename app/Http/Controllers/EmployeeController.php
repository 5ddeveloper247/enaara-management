<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Employee\EmployeeStoreRequest;
use App\Http\Requests\Admin\Employee\EmployeeUpdateRequest;
use App\Services\DesignationService;
use App\Services\EmployeeService;
use App\Services\EmployeeViewerScopeService;
use App\Services\UniversityDirectoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Department;
use App\Models\Employee;

class EmployeeController extends Controller
{
    private EmployeeService $employeeService;
    private UniversityDirectoryService $universityDirectoryService;
    private DesignationService $designationService;
    private EmployeeViewerScopeService $viewerScope;

    public function __construct(
        EmployeeService $employeeService,
        UniversityDirectoryService $universityDirectoryService,
        DesignationService $designationService,
        EmployeeViewerScopeService $viewerScope,
    ) {
        $this->employeeService = $employeeService;
        $this->universityDirectoryService = $universityDirectoryService;
        $this->designationService = $designationService;
        $this->viewerScope = $viewerScope;
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
            Log::info('Employee store request received', [
                'user_id' => Auth::id(),
                'name' => trim($request->input('first_name') . ' ' . $request->input('last_name')),
            ]);

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
                    'filter_role'          => $request->query('filter_role'),
                    'filter_gender'        => $request->query('filter_gender'),
                    'filter_name'          => $request->query('filter_name'),
                    'filter_cnic'          => $request->query('filter_cnic'),
                ]),
            ]);
        } catch (\Exception $e) {
            Log::error('Employee table data failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'data' => []], 500);
        }
    }

    public function universities(): JsonResponse
    {
        if (! validatePermissions('admin/employees')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                'data' => [],
            ], 403);
        }

        try {
            $universities = $this->universityDirectoryService->pakistanUniversities();

            return response()->json([
                'success' => true,
                'message' => 'Universities fetched successfully.',
                'data' => $universities,
            ]);
        } catch (\Throwable $e) {
            Log::error('Universities directory fetch failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch universities right now.',
                'data' => [],
            ], 500);
        }
    }

    public function designationsForEmployment(Request $request): JsonResponse
    {
        if (! validatePermissions('admin/employees')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                'data' => [],
            ], 403);
        }

        $orgId = (int) $request->input('organization_id');
        $sbuId = (int) $request->input('sbu_id');

        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
            'sbu_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('sbus', 'id')->where(fn ($q) => $q->where('organization_id', $orgId)),
            ],
            'department_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('departments', 'id')->where(fn ($q) => $q->where('organization_id', $orgId)->where('sbu_id', $sbuId)),
            ],
            'department_ids' => ['nullable', 'array', 'min:1'],
            'department_ids.*' => [
                'integer',
                \Illuminate\Validation\Rule::exists('departments', 'id')->where(fn ($q) => $q->where('organization_id', $orgId)->where('sbu_id', $sbuId)),
            ],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ]);

        $departmentIds = $validated['department_ids'] ?? [];
        if (! is_array($departmentIds) || $departmentIds === []) {
            $singleDepartmentId = (int) ($validated['department_id'] ?? 0);
            $departmentIds = $singleDepartmentId > 0 ? [$singleDepartmentId] : [];
        }

        if ($departmentIds === []) {
            return response()->json([
                'success' => false,
                'message' => 'At least one department is required.',
                'data' => [],
            ], 422);
        }

        try {
            $this->viewerScope->assertSbuIdAllowed((int) $validated['sbu_id']);
            $this->viewerScope->assertDepartmentIdsAllowed($departmentIds);
            $data = $this->designationService->listActiveByOrganizationSbuAndDepartments(
                (int) $validated['organization_id'],
                (int) $validated['sbu_id'],
                $departmentIds
            );

            return response()->json([
                'success' => true,
                'message' => 'Designations loaded successfully.',
                'data' => $data,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors' => $e->errors(),
                'data' => [],
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Employee designations list failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load designations.',
                'data' => [],
            ], 500);
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

    public function checkLineManagerAvailability(Request $request): JsonResponse
    {
        if (! validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to perform this action.'], 403);
        }

        $validated = $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'integer', 'exists:employees,id'],
        ]);

        try {
            $this->viewerScope->assertDepartmentIdAllowed((int) $validated['department_id']);
            if (! empty($validated['employee_id'])) {
                $this->viewerScope->assertEmployeeIdAccessible((int) $validated['employee_id']);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Unauthorized.',
            ], 403);
        }

        $departmentId = (int) $validated['department_id'];
        $excludeId = isset($validated['employee_id']) ? (int) $validated['employee_id'] : null;

        $existing = $this->employeeService->findDepartmentLineManager($departmentId, $excludeId);

        return response()->json([
            'success' => true,
            'available' => $existing === null,
            'existing_manager' => $existing ? [
                'id' => $existing->id,
                'full_name' => $existing->full_name,
                'employee_code' => $existing->employee_code,
            ] : null,
            'department_name' => Department::query()->where('id', $departmentId)->value('name'),
        ]);
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
            $sbuId = isset($validated['sbu_id']) ? (int) $validated['sbu_id'] : null;
            if ($sbuId === null || $sbuId <= 0) {
                $scope = $this->viewerScope->frontendScopePayload();
                if (! empty($scope['restricted']) && ! empty($scope['sbu_id'])) {
                    $sbuId = (int) $scope['sbu_id'];
                }
            }
            $this->viewerScope->assertSbuIdAllowed($sbuId);
            $code = $this->employeeService->previewNextEmployeeCode(
                (int) $validated['organization_id'],
                (int) $validated['role_id'],
                $sbuId
            );

            return response()->json(['success' => true, 'code' => $code]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Unauthorized.',
            ], 403);
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
            $syncAttachments = $request->has('kept_attachment_ids') || $request->has('attachments') || $request->hasFile('attachments');
            $employee = $this->employeeService->update($id, $request->validated(), $photos, $attachments, $keptAttachmentIds, $syncAttachments);

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
            if ($employeeId) {
                $this->viewerScope->assertEmployeeIdAccessible((int) $employeeId);
            }
            $data = $request->except(['_token', 'employee_id', 'kept_attachment_ids', 'attachments', 'profile_photo']);

            $photos   = $request->hasFile('profile_photo') ? [$request->file('profile_photo')] : [];
            $attachments = $this->extractAttachments($request);
            $keptAttachmentIds = array_values(array_filter(array_map('intval', $request->input('kept_attachment_ids', []))));
            $syncAttachments = $request->has('kept_attachment_ids') || $request->has('attachments') || $request->hasFile('attachments');

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
                $employee = $this->employeeService->update((int)$employeeId, $data, $photos, $attachments, $keptAttachmentIds, $syncAttachments);
                $message = $moduleName . ' saved successfully.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'next_step' => $step + 1
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
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

            $this->viewerScope->assertEmployeeIdAccessible((int) $employeeId);

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
                    
                    // Handle academic transcript file if provided
                    if ($request->hasFile('transcript_file') && $record) {
                        $file = $request->file('transcript_file');
                        $attachmentData = [
                            'name' => 'Academic Transcript - ' . $record->degree,
                            'type' => 'Academic Transcript',
                            'description' => 'Uploaded academic transcript for ' . $record->degree,
                            'files' => [$file]
                        ];
                        $saved = $this->employeeService->saveSingleAttachment((int)$employeeId, $attachmentData);
                        if (!empty($saved)) {
                            // Link to subsection
                            $saved[0]->update(['subsection' => 'academic_' . $record->id . '_transcript']);
                            $responseData['transcript_url'] = asset('storage/' . $saved[0]->file_path);
                            $responseData['transcript_id'] = $saved[0]->id;
                        }
                    }

                    // Handle academic degree file if provided
                    if ($request->hasFile('degree_file') && $record) {
                        $file = $request->file('degree_file');
                        $attachmentData = [
                            'name' => 'Academic Degree - ' . $record->degree,
                            'type' => 'Academic Degree',
                            'description' => 'Uploaded academic degree for ' . $record->degree,
                            'files' => [$file]
                        ];
                        $saved = $this->employeeService->saveSingleAttachment((int)$employeeId, $attachmentData);
                        if (!empty($saved)) {
                            // Link to subsection
                            $saved[0]->update(['subsection' => 'academic_' . $record->id . '_degree']);
                            $responseData['degree_url'] = asset('storage/' . $saved[0]->file_path);
                            $responseData['degree_id'] = $saved[0]->id;
                        }
                    }
                    
                    $message = 'Academic record added successfully.';
                    $responseData = array_merge($responseData ?? [], ['id' => $record?->id]);
                    break;
                case 'certificate_row':
                    $record = $this->employeeService->saveCertificate((int)$employeeId, $data);
                    
                    if ($record && $request->hasFile('certificate_file')) {
                        $file = $request->file('certificate_file');
                        $attachmentData = [
                            'name' => 'Certificate - ' . $record->certificate_name,
                            'type' => 'Professional Certificate',
                            'description' => 'Uploaded document for ' . $record->certificate_name,
                            'files' => [$file]
                        ];
                        $saved = $this->employeeService->saveSingleAttachment((int)$employeeId, $attachmentData);
                        if (!empty($saved)) {
                            $saved[0]->update(['subsection' => 'certificate_' . $record->id]);
                            $responseData['attachment_url'] = asset('storage/' . $saved[0]->file_path);
                            $responseData['attachment_id'] = $saved[0]->id;
                        }
                    }

                    $message = 'Certificate record added successfully.';
                    $responseData['id'] = $record?->id;
                    break;
                case 'employment_row':
                    $record = $this->employeeService->saveExEmployment((int)$employeeId, $data);
                    
                    if ($record) {
                        // Handle Experience Letter
                        if ($request->hasFile('experience_letter')) {
                            $file = $request->file('experience_letter');
                            $attachmentData = [
                                'type' => 'Experience Letter',
                                'name' => 'Experience Letter',
                                'description' => 'Ex-Employment Experience Letter',
                                'files' => [$file]
                            ];
                            $saved = $this->employeeService->saveSingleAttachment((int)$employeeId, $attachmentData);
                            if (!empty($saved)) {
                                $saved[0]->update(['subsection' => 'ex_employment_' . $record->id . '_exp']);
                                $responseData['exp_letter_url'] = asset('storage/' . $saved[0]->file_path);
                                $responseData['exp_letter_id'] = $saved[0]->id;
                            }
                        }
                        
                        // Handle Salary Slip
                        if ($request->hasFile('salary_slip')) {
                            $file = $request->file('salary_slip');
                            $attachmentData = [
                                'type' => 'Salary Slip',
                                'name' => 'Salary Slip',
                                'description' => 'Ex-Employment Salary Slip',
                                'files' => [$file]
                            ];
                            $saved = $this->employeeService->saveSingleAttachment((int)$employeeId, $attachmentData);
                            if (!empty($saved)) {
                                $saved[0]->update(['subsection' => 'ex_employment_' . $record->id . '_salary']);
                                $responseData['salary_slip_url'] = asset('storage/' . $saved[0]->file_path);
                                $responseData['salary_slip_id'] = $saved[0]->id;
                            }
                        }
                    }

                    $message = 'Employment history record added successfully.';
                    $responseData['id'] = $record?->id;
                    break;
                case 'medical':
                    $this->employeeService->saveMedical((int)$employeeId, $data);

                    if ($request->hasFile('medical_file')) {
                        $file = $request->file('medical_file');
                        $attachmentData = [
                            'name' => 'Medical Report / Fitness Certificate',
                            'type' => 'Medical Document',
                            'description' => 'Uploaded medical fitness document',
                            'files' => [$file]
                        ];
                        $saved = $this->employeeService->saveSingleAttachment((int)$employeeId, $attachmentData);
                        if (!empty($saved)) {
                            $saved[0]->update(['subsection' => 'medical']);
                            $responseData['attachment_url'] = asset('storage/' . $saved[0]->file_path);
                            $responseData['attachment_id'] = $saved[0]->id;
                        }
                    }

                    $message = 'Medical Information saved successfully.';
                    break;
                case 'references':
                    $this->employeeService->saveReferences((int)$employeeId, $data);
                    $message = 'References saved successfully.';
                    break;
                case 'bank_row':
                    $validated = $request->validated();
                    $row = Arr::only($validated, [
                        'account_category', 'account_title', 'account_no', 'bank_name',
                        'branch_name', 'branch_code', 'branch_address', 'iban', 'account_type', 'is_salary_account',
                        'bank_detail_id'
                    ]);
                    try {
                        $record = $this->employeeService->saveBankDetailRow(
                            (int) $employeeId,
                            $row,
                            (isset($validated['bank_detail_id']) && $validated['bank_detail_id'] !== null && $validated['bank_detail_id'] !== '') ? (int) $validated['bank_detail_id'] : null
                        );
                    } catch (ValidationException $e) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Validation failed.',
                            'errors' => $e->errors(),
                        ], 422);
                    } catch (\InvalidArgumentException $e) {
                        return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                    }
                    $message = 'Bank account saved successfully.';
                    $responseData = [
                        'id' => $record->id,
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
                case 'photo':
                    if ($request->hasFile('profile_photo')) {
                        $this->employeeService->savePhoto((int)$employeeId, $request->file('profile_photo'));
                        $message = 'Profile photo saved successfully.';
                    } else {
                        return response()->json(['success' => false, 'message' => 'No photo uploaded.'], 422);
                    }
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

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
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

    public function deleteCertificate(Request $request)
    {
        return $this->processDeletion($request, 'certificate_row');
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
            $this->viewerScope->assertEmployeeIdAccessible((int) $validated['employee_id']);
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
        if (! validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $employeeId = $request->input('employee_id');
        if (!$employeeId) {
            return response()->json(['success' => false, 'message' => 'Employee must be saved before adding attachments.'], 422);
        }

        try {
            $this->viewerScope->assertEmployeeIdAccessible((int) $employeeId);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Unauthorized.',
            ], 403);
        }

        try {
            $attachments = $request->input('attachments', []);
            $attachmentData = $attachments[0] ?? [];
            
            $reqSubsection = $request->input('subsection');
            $attachmentData['subsection'] = ($reqSubsection === 'attachment') ? null : $reqSubsection;
            
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

    public function attachments(int $id)
    {
        if (!validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $this->viewerScope->assertEmployeeIdAccessible($id);
            $employee = \App\Models\Employee::query()->findOrFail($id);
            $attachments = $this->employeeService->attachmentsForEditPayload($employee);

            return response()->json([
                'success' => true,
                'attachments' => $attachments,
            ]);
        } catch (\Exception $e) {
            Log::error('Employee attachments fetch failed', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch attachments.',
            ], 500);
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
