<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Employee\OutsourcedEmployeeStoreRequest;
use App\Http\Requests\Admin\Employee\OutsourcedEmployeeUpdateRequest;
use App\Services\OutsourcedEmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OutsourcedEmployeeController extends Controller
{
    public function __construct(private readonly OutsourcedEmployeeService $outsourcedEmployeeService)
    {
    }

    public function tableData(Request $request): JsonResponse
    {
        if (! validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            return response()->json([
                'success' => true,
                'data' => $this->outsourcedEmployeeService->getTableData([
                    'filter_organization' => $request->query('filter_organization'),
                    'filter_sbu' => $request->query('filter_sbu'),
                    'filter_department' => $request->query('filter_department'),
                    'filter_name' => $request->query('filter_name'),
                    'filter_cnic' => $request->query('filter_cnic'),
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Outsourced employee tableData failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'data' => []], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        if (! validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $row = $this->outsourcedEmployeeService->findForEdit($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $row->id,
                    'full_name' => $row->full_name,
                    'cnic_number' => $row->cnic_number,
                    'mobile_number' => $row->mobile_number,
                    'photo_url' => $row->photo_path ? asset('storage/' . $row->photo_path) : null,
                    'contractor_company_name' => $row->contractor_company_name,
                    'supervisor_name' => $row->supervisor_name,
                    'supervisor_contact_number' => $row->supervisor_contact_number,
                    'organization_id' => $row->organization_id,
                    'organization' => $row->organization?->name ?? '-',
                    'sbu_id' => $row->sbu_id,
                    'sbu' => $row->sbu?->name ?? '-',
                    'department_id' => $row->department_id,
                    'department' => $row->department?->name ?? '-',
                    'job_role_trade' => $row->job_role_trade,
                    'placement_floor' => $row->placement_floor,
                    'date_of_deployment' => optional($row->date_of_deployment)->format('Y-m-d'),
                    'biometric_id' => $row->biometric_id,
                    'attendance_access' => (bool) $row->attendance_access,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Outsourced employee show failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
        }
    }

    public function store(OutsourcedEmployeeStoreRequest $request): JsonResponse
    {
        if (! validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $row = $this->outsourcedEmployeeService->store(
                $request->validated(),
                $request->file('photo')
            );
            return response()->json([
                'success' => true,
                'message' => 'Outsourced employee added successfully.',
                'id' => $row->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Outsourced employee store failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(OutsourcedEmployeeUpdateRequest $request, int $id): JsonResponse
    {
        if (! validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $row = $this->outsourcedEmployeeService->update(
                $id,
                $request->validated(),
                $request->file('photo')
            );
            return response()->json([
                'success' => true,
                'message' => 'Outsourced employee updated successfully.',
                'id' => $row->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Outsourced employee update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

