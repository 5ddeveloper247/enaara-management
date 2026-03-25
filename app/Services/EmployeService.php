<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class EmployeService
{
    public function index(): View
    {
        $organizations = Organization::with('sbus.departments')->get();
        return view('admin.employee.index', compact('organizations'));
    }

    public function store(array $data): Employee
    {
        Log::info('EmployeService::store() called', ['data' => $data]);

        $employee = Employee::create([
            'full_name'           => $data['full_name'],
            'employee_code'       => $data['employee_id'] ?? null,
            'email'               => $data['email'],
            'phone'               => $data['phone'] ?? null,
            'organization_id'     => $data['organization_id'] ?? null,
            'sbu_id'              => $data['sbu_id'] ?? null,
            'department_id'       => $data['department_id'] ?? null,
            'employee_type'       => $data['employee_type'] ?? null,
            'employment_type'     => $data['employment_type'] ?? null,
            'site'                => $data['site_assignment'] ?? null,
            'join_date'           => $data['join_date'] ?? null,
            'floor_access'        => isset($data['floor_access_10']) ? (bool) $data['floor_access_10'] : false,
            'biometric_id'        => $data['biometric_id'] ?? null,
            'sync_with_biometric' => isset($data['sync_with_biometric']) ? (bool) $data['sync_with_biometric'] : false,
            'is_active'           => true,
        ]);

        Log::info('Employee created successfully', ['employee_id' => $employee->id, 'email' => $employee->email]);

        return $employee;
    }
}
