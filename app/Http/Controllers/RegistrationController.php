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
        if ($request->has('id')) {
            $employee = Employee::find($request->id);
        }
        $data['employee'] = $employee;
        return view('admin.employeeregistration.index', $data);
    }
}
