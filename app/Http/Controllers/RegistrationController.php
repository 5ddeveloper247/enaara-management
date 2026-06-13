<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\EmployeeService;
use App\Services\EmployeeViewerScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    private EmployeeService $employeeService;

    private EmployeeViewerScopeService $viewerScope;

    public function __construct(EmployeeService $employeeService, EmployeeViewerScopeService $viewerScope)
    {
        $this->employeeService = $employeeService;
        $this->viewerScope = $viewerScope;
    }

    public function index(Request $request): View
    {
        $data = $this->employeeService->getFormData();
        $employee = null;
        $editData = [];

        if ($request->filled('id')) {
            $employee = Employee::find((int) $request->id);
            if ($employee) {
                try {
                    $this->viewerScope->assertEmployeeIdAccessible((int) $employee->id);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    abort(403, collect($e->errors())->flatten()->first() ?? 'Unauthorized.');
                }
                $editData['attachments'] = $this->employeeService->attachmentsForEditPayload($employee);
            }
        }

        $data['employee'] = $employee;
        $data['editData'] = $editData;

        return view('admin.employeeregistration.index', $data);
    }

    public function addDocumentType(Request $request)
    {
        if (! validatePermissions('admin/employees')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $type = $this->employeeService->addRequiredDocumentType($request->name);

        return response()->json([
            'success' => true,
            'data' => $type,
        ]);
    }
}
