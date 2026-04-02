<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    private EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(): View
    {
        $data = $this->employeeService->getFormData();
        return view('admin.register.index', $data);
    }
}
