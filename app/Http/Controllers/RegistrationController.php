<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    private EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(Request $request): View
    {
        $data = $this->employeeService->getFormData();
        $employee = null;
        $editData = [];
        if ($request->filled('id')) {
            $employee = Employee::find((int) $request->id);
            if ($employee) {
                $editData['attachments'] = $this->employeeService->attachmentsForEditPayload($employee);
            }
        }
        $data['employee'] = $employee;
        $data['editData'] = $editData;
        return view('admin.employeeregistration.index', $data);
    }
}
