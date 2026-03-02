<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function __construct(
        private LeaveTypeService $leaveTypeService
    ) {}

    public function index(): View
    {
        $leaveTypes = $this->leaveTypeService->getList();
        $organizations = $this->leaveTypeService->getOrganizationsForFilter();
        $counts = $this->leaveTypeService->getCounts();

        return view('admin.leave-type.index', [
            'leaveTypes' => $leaveTypes,
            'organizations' => $organizations,
            'total' => $counts['total'],
            'active' => $counts['active'],
            'inactive' => $counts['inactive'],
        ]);
    }

    public function create(): View
    {
        $organizations = $this->leaveTypeService->getOrganizationsForFilter();

        return view('admin.leave-type.create', [
            'organizations' => $organizations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('leave_types')->where('organization_id', $request->input('organization_id')),
            ],
            'annual_quota' => 'required|numeric|min:0|max:999.99',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $this->leaveTypeService->create($validated);

        return redirect()->route('admin.leave.type.index')
            ->with('success', 'Leave type created successfully.');
    }

    public function edit(int $id): View
    {
        $leaveType = $this->leaveTypeService->findById($id);

        if (!$leaveType instanceof LeaveType) {
            abort(404);
        }

        $organizations = $this->leaveTypeService->getOrganizationsForFilter();

        return view('admin.leave-type.edit', [
            'leaveType' => $leaveType,
            'organizations' => $organizations,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $leaveType = $this->leaveTypeService->findById($id);

        if (!$leaveType instanceof LeaveType) {
            abort(404);
        }

        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('leave_types')
                    ->where('organization_id', $request->input('organization_id'))
                    ->ignore($leaveType->id),
            ],
            'annual_quota' => 'required|numeric|min:0|max:999.99',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $this->leaveTypeService->update($leaveType, $validated);

        return redirect()->route('admin.leave.type.index')
            ->with('success', 'Leave type updated successfully.');
    }
}

