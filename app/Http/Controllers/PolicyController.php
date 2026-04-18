<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\Policy\StorePolicyRequest;
use App\Http\Requests\Admin\Policy\UpdatePolicyRequest;
use App\Models\Organization;
use App\Models\Policy;
use App\Services\PolicyService;

class PolicyController extends Controller
{
    protected $policyService;

    public function __construct(PolicyService $policyService)
    {
        $this->policyService = $policyService;
    }

    public function index()
    {
        if (! validatePermissions('admin/policies')) {
            abort(403, 'Unauthorized action.');
        }

        $policies = Policy::orderBy('updated_at', 'desc')->get();

        $organizations = Organization::query()
            ->where('is_active', true)
            ->with([
                'sbus' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
                'sbus.floors' => fn ($q) => $q->where('is_active', true)->orderBy('floor_number'),
            ])
            ->orderBy('name')
            ->get();

        $policyScopeTree = $organizations->map(fn (Organization $org) => [
            'id' => $org->id,
            'name' => $org->name,
            'sbus' => $org->sbus->map(fn ($sbu) => [
                'id' => $sbu->id,
                'name' => $sbu->name,
                'floors' => $sbu->floors->map(fn ($floor) => [
                    'id' => $floor->id,
                    'name' => $floor->name,
                    'floor_number' => $floor->floor_number,
                ]),
            ]),
        ]);

        $organizationsForFilter = $organizations->map(fn (Organization $org) => [
            'id' => $org->id,
            'name' => $org->name,
        ]);

        return view('admin.policies.index', compact('policies', 'policyScopeTree', 'organizationsForFilter'));
    }

    public function show($id)
    {
        if (! validatePermissions('admin/policies')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $policy = Policy::findOrFail($id);

            return response()->json([
                'success' => true,
                'policy' => $policy,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Policy not found.'], 404);
        }
    }

    public function store(StorePolicyRequest $request)
    {
        if (! validatePermissions('admin/policies')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $file = $request->hasFile('document') ? $request->file('document') : null;
            $this->policyService->store($request->validated(), $file);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Policy created successfully.',
                ]);
            }

            return redirect()
                ->route('admin.policies.index')
                ->with('success', 'Policy created successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create policy: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create policy: '.$e->getMessage());
        }
    }

    public function update(UpdatePolicyRequest $request, $id)
    {
        if (! validatePermissions('admin/policies')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $file = $request->hasFile('document') ? $request->file('document') : null;
            $this->policyService->update($request->validated(), $id, $file);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Policy updated successfully.',
                ]);
            }

            return redirect()
                ->route('admin.policies.index')
                ->with('success', 'Policy updated successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update policy: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update policy: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (! validatePermissions('admin/policies')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        try {
            $this->policyService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Policy deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete policy: '.$e->getMessage(),
            ], 500);
        }
    }
}
