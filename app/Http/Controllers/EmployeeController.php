<?php

namespace App\Http\Controllers;

use App\Services\EmployeService;
use App\Http\Requests\Admin\Employe\EmployeeStoreRequest;

class EmployeeController extends Controller
{
    private EmployeService $employeeService;

    public function __construct(EmployeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index()
    {
        return $this->employeeService->index();
    }

    public function store(EmployeeStoreRequest $request)
    {
        if (!validatePermissions('admin/employee/add')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $result = $this->employeeService->store($request->validated());
            return redirect()
                ->route('admin.employee.index')
                ->with('success', 'Employee created successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
