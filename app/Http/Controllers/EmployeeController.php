<?php

namespace App\Http\Controllers;

use App\Services\EmployeService;
use App\Http\Requests\Admin\Employe\EmployeeStoreRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        if (!validatePermissions('admin/employee')) {
            Log::warning('Unauthorized employee create attempt', ['user_id' => Auth::id()]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.',
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            Log::info('Employee store request received', [
                'user_id'   => Auth::id(),
                'input'     => $request->validated(),
            ]);

            $employee = $this->employeeService->store($request->validated());

            Log::info('Employee stored successfully', ['employee_id' => $employee->id]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Employee created successfully.',
                    'employee_id' => $employee->id,
                ]);
            }

            return redirect()
                ->route('admin.employee.index')
                ->with('success', 'Employee created successfully!');

        } catch (\Exception $e) {
            Log::error('Employee store failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again.',
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
