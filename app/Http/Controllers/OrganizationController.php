<?php

namespace App\Http\Controllers;

use App\Services\OrganizationService;
use Illuminate\View\View;
use App\Http\Requests\Admin\Organization\OrganizationStoreRequest;
use App\Http\Requests\Admin\Organization\OrganizationUpdateRequest;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationService $organizationService
    ) {}

    public function index(): View
    {
        if (!validatePermissions('admin/organization')) {
            abort(403, 'Unauthorized action.');
        }

        $organizations = $this->organizationService->getOrganizationsList();
        $counts = $this->organizationService->getOrganizationsCounts();

        return view('admin.organization.index', [
            'organizations' => $organizations,
            'totalOrganizations' => $counts['total'],
            'activeOrganizations' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    public function create(OrganizationStoreRequest $request)
    {
        if (!validatePermissions('admin/organization/add')) {
            abort(403, 'Unauthorized action.');
        }
        return view('admin.organization.index', [
            'organizations' => $organizations,
            'totalOrganizations' => $counts['total'],
            'activeOrganizations' => $counts['active'],
            'activePercentage' => $counts['active_percentage'],
        ]);
    }

    // Store a newly created organization.
    public function store(OrganizationStoreRequest $request)
    {
        if (!validatePermissions('admin/organization/add')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->organizationService->store($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Organization created successfully.',
                ]);
            }

            return redirect()
                ->route('admin.organization.index')
                ->with('success', 'Organization created successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create organization: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create organization: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        if (!validatePermissions('admin/organization/edit')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $organization = $this->organizationService->findById($id);

            if (!$organization) {
                return response()->json([
                    'success' => false,
                    'message' => 'Organization not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $organization->id,
                    'parent_id' => $organization->parent_id,
                    'name' => $organization->name,
                    'code' => $organization->code,
                    'email' => $organization->email,
                    'tax_no' => $organization->tax_no,
                    'description' => $organization->description,
                    'address' => $organization->address,
                    'working_days' => $organization->working_days,
                    'working_start_time' => $organization->working_start_time,
                    'working_end_time' => $organization->working_end_time,
                    'opening_grace_period' => $organization->opening_grace_period,
                    'closing_grace_period' => $organization->closing_grace_period,
                    'is_active' => $organization->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch organization: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(OrganizationUpdateRequest $request, $id)
    {
        if (!validatePermissions('admin/organization/edit')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->organizationService->update($id, $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Organization updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.organization.index')
                ->with('success', 'Organization updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update organization: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update organization: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!validatePermissions('admin/organization/delete')) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        try {
            $this->organizationService->destroy($id);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Organization deleted successfully.',
                ]);
            }

            return redirect()
                ->route('admin.organization.index')
                ->with('success', 'Organization deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete organization: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Failed to delete organization: ' . $e->getMessage());
        }
    }
}
