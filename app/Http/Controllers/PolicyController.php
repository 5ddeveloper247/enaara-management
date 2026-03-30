<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PolicyService;
use App\Http\Requests\Admin\Policy\StorePolicyRequest;
use App\Http\Requests\Admin\Policy\UpdatePolicyRequest;
use App\Models\Policy;

class PolicyController extends Controller
{
    protected $policyService;

    public function __construct(PolicyService $policyService)
    {
        $this->policyService = $policyService;
    }

    public function index()
    {
        if (!validatePermissions('admin/policies')) {
            abort(403, 'Unauthorized action.');
        }

        $policies = Policy::orderBy('updated_at', 'desc')->get();

        return view('admin.policies.index', compact('policies'));
    }

    public function show($id)
    {
        if (!validatePermissions('admin/policies')) {
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
        if (!validatePermissions('admin/policies')) {
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
                    'message' => 'Failed to create policy: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create policy: ' . $e->getMessage());
        }
    }

    public function update(UpdatePolicyRequest $request, $id)
    {
        if (!validatePermissions('admin/policies')) {
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
                    'message' => 'Failed to update policy: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update policy: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!validatePermissions('admin/policies')) {
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
                'message' => 'Failed to delete policy: ' . $e->getMessage(),
            ], 500);
        }
    }
}
